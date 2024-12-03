<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Exports\ReservationsExport;
use Maatwebsite\Excel\Facades\Excel;
use OpenSpout\Common\Entity\Style\Style;
use Rap2hpoutre\FastExcel\FastExcel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationConfirmation;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $reservations = DB::table('reservations')
                ->join('customers', 'reservations.customerId', '=', 'customers.customerId')
                ->select([
                    'reservations.reservationId',
                    'reservations.customerId',
                    'customers.name',
                    'reservations.pax',
                    'reservations.reservationDate',
                    'reservations.eventType',
                    'reservations.created_at'
                ])
                ->whereNotIn('reservations.rstatus', ['cancel', 'completed'])
                ->orderBy('reservations.created_at', 'desc')
                ->get();

            return response()->json([
                'data' => $reservations
            ]);
        }

        if ($request->has('edit')) {
            // If there's an edit parameter, get the reservation details
            $reservationId = $request->query('edit');
            $reservation = DB::table('reservations')
                ->where('reservationId', $reservationId)
                ->first();
                
            if ($reservation) {
                // Pass the reservation data to the view
                return view('reservation', ['editReservation' => $reservation]);
            }
        }

        return view('reservation');
    }

    // Change this part to return the correct view
    
    public function checkCustomer(Request $request)
    {
        $customerId = strtoupper(trim($request->customerId));
        $exists = DB::table('customers')
            ->where('customerId', $customerId)
            ->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists 
                ? '<span style="color:green">Customer ID exists.</span>'
                : '<span style="color:red">None record.</span>'
        ]);
    }

    public function checkCustomerStatus(Request $request)
    {
        $customerId = strtoupper(trim($request->customerId));
        $customer = DB::table('customers')
            ->where('customerId', $customerId)
            ->first();

        $isActive = $customer && $customer->status === 'Active';

        return response()->json([
            'exists' => $isActive,
            'message' => $isActive 
                ? '<span style="color:green"> Status: Active</span>'
                : '<span style="color:red"> Status: ' . ($customer ? $customer->status : 'Not Found') . '</span>'
        ]);
    }

    public function getCustomerDetail(Request $request)
    {
        $customerId = $request->customerId;
        
        // Add some logging to debug
        \Log::info('Searching for customer: ' . $customerId);
        
        $customer = DB::table('customers')
            ->where('customerId', $customerId)
            ->first();
        
        // Add logging for the result
        \Log::info('Found customer:', ['customer' => $customer]);
        
        return response()->json([
            'name' => $customer ? $customer->name : '', // Make sure the column name matches your database
            'success' => true
        ]);
    }

    public function store(Request $request)
    {
        // Check if it's a customer reservation
        if ($request->is('api/*')) {
            try {
                // Get customer details with logging
                $customer = DB::table('customers')
                    ->where('customerId', $request->customerId)
                    ->first();
                
                \Log::info('Customer details:', [
                    'customerId' => $request->customerId,
                    'email' => $customer ? $customer->email : 'not found',
                    'name' => $customer ? $customer->name : 'not found'
                ]);
            
                // Create reservation - ensure rstatus is 'firstc'
                $reservation = [
                    'reservationId' => $request->reservationId,
                    'customerId' => $request->customerId,
                    'pax' => $request->pax,
                    'reservationDate' => $request->reservationDate,
                    'rarea' => $request->rarea,
                    'reservedBy' => 'customer',
                    'rstatus' => 'firstc',  // Make sure this is always 'firstc'
                    'created_at' => now(),
                ];
            
                DB::table('reservations')->insert($reservation);

                // Send confirmation email with error handling
                if ($customer && $customer->email) {
                    try {
                        Mail::send('emails.confirmation_firstc', [
                            'reservation' => (object)[
                                'reservationId' => $request->reservationId,
                                'customer_name' => $customer->name,
                                'pax' => $request->pax,
                                'reservationDate' => $request->reservationDate,
                                'rarea' => $request->rarea
                            ]
                        ], function($message) use ($customer) {
                            $message->to($customer->email)
                                ->subject('New Reservation Confirmation Required');
                            
                            \Log::info('Sending email to customer:', [
                                'email' => $customer->email,
                                'name' => $customer->name
                            ]);
                        });
                    } catch (\Exception $e) {
                        \Log::error('Email sending failed', [
                            'error' => $e->getMessage(),
                            'customer_email' => $customer->email
                        ]);
                    }
                } else {
                    \Log::error('Customer email not found', [
                        'customerId' => $request->customerId
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Reservation created successfully'
                ], 201);

            } catch (\Exception $e) {
                \Log::error('Reservation creation failed', [
                    'error' => $e->getMessage(),
                    'customerId' => $request->customerId
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create reservation: ' . $e->getMessage()
                ], 500);
            }
        } else {
            // Your existing admin store method
            try {
                // Validate the request
                $validated = $request->validate([
                    'reservationId' => 'required|unique:reservations,reservationId',
                    'customerId' => 'required',
                    'orderId' => 'nullable',
                    'paymentId' => 'nullable',
                    'pax' => 'required|integer|min:1',
                    'reservation_date' => 'required',
                    'event' => 'nullable',
                    'remark' => 'nullable',
                    'rarea' => 'required|in:W,C',
                    'reservedBy' => 'required',
                    'rstatus' => 'required'
                ]);

                // Create the reservation
                DB::table('reservations')->insert([
                    'reservationId' => $validated['reservationId'],
                    'customerId' => $validated['customerId'],
                    'orderId' => $validated['orderId'],
                    'paymentId' => null,
                    'pax' => $validated['pax'],
                    'reservationDate' => $validated['reservation_date'],
                    'eventType' => $validated['event'],
                    'remark' => $validated['remark'],
                    'rarea' => $validated['rarea'],
                    'reservedBy' => $validated['reservedBy'],
                    'rstatus' => $validated['rstatus'],
                    'created_at' => now()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Reservation created successfully'
                ], 201);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create reservation: ' . $e->getMessage()
                ], 500);
            }
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'orderId' => 'nullable',
                'paymentId' => 'nullable',
                'pax' => 'required|integer|min:1',
                'reservation_date' => 'required',
                'event' => 'nullable',
                'remark' => 'nullable',
                'rstatus' => 'required'
            ]);

            DB::table('reservations')
                ->where('reservationId', $id)
                ->update([
                    'orderId' => $validated['orderId'],
                    'paymentId' => $validated['paymentId'],
                    'pax' => $validated['pax'],
                    'reservationDate' => $validated['reservation_date'],
                    'eventType' => $validated['event'],
                    'remark' => $validated['remark'],
                    'rstatus' => $validated['rstatus'],
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Reservation updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating reservation: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        try {
            $reservation = DB::table('reservations')
                ->where('reservationId', $id)
                ->first();

            if ($reservation) {
                return response()->json([
                    'success' => true,
                    'data' => $reservation
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Reservation not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching reservation: ' . $e->getMessage()
            ], 500);
        }
    }

    public function export($type = 'xlsx') 
    {
        $reservations = DB::table('reservations')
            ->join('customers', 'reservations.customerId', '=', 'customers.customerId')
            ->select(
                'reservations.reservationId as Reservation ID',
                'reservations.customerId as Customer ID',
                'customers.name as Customer Name',
                'reservations.pax as Number of Guests',
                'reservations.reservationDate as Reservation Date',
                'reservations.eventType as Event',
                'reservations.orderId as Order ID',
                'reservations.paymentId as Payment ID',
                DB::raw("CASE 
                    WHEN reservations.rarea = 'W' THEN 'Rajah Room'
                    WHEN reservations.rarea = 'C' THEN 'Hornbill Restaurant'
                    ELSE reservations.rarea 
                END as Area"),
                'reservations.remark as Remark'
            )
            ->get();

        switch($type) {
            case 'csv':
                return (new FastExcel($reservations))->download('reservations.csv');
                
            case 'pdf':
                $pdf = PDF::loadView('exports.reservations-pdf', ['reservations' => $reservations]);
                return $pdf->download('reservations.pdf');
                
            default: // xlsx
                return (new FastExcel($reservations))->download('reservations.xlsx');
        }
    }

    public function destroy($id)
    {
        try {
            DB::table('reservations')
                ->where('reservationId', $id)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Reservation deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting reservation: ' . $e->getMessage()
            ], 500);
        }
    }

    public function template()
    {
        $headers = [
            'Customer ID',
            'Number of Guests',
            'Reservation Date (YYYY-MM-DD HH:mm)',
            'Event',
            'Area (W/C)',
            'Status (C/P)',  // Added status field
            'Remark'
        ];

        $f = fopen('php://memory', 'r+');
        fputcsv($f, $headers);
        
        rewind($f);
        $content = stream_get_contents($f);
        fclose($f);

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="reservation_template.csv"');
    }

    public function import(Request $request)
    {
        try {
            if (!$request->hasFile('csvFile')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No file uploaded'
                ], 400);
            }

            $file = $request->file('csvFile');
            $rows = array_map('str_getcsv', file($file->getPathname()));
            $header = array_shift($rows);

            $successCount = 0;
            $errorRows = [];

            foreach ($rows as $index => $row) {
                try {
                    // Validate customer ID
                    $customer = DB::table('customers')
                        ->where('customerId', $row[0])
                        ->first();

                    if (!$customer) {
                        $errorRows[] = "Row " . ($index + 2) . ": Customer ID not found";
                        continue;
                    }

                    // Validate area
                    $area = strtoupper($row[4]);
                    if (!in_array($area, ['W', 'C'])) {
                        $errorRows[] = "Row " . ($index + 2) . ": Invalid area (must be W or C)";
                        continue;
                    }

                    // Validate status
                    $status = strtoupper($row[5]);
                    if (!in_array($status, ['C', 'P'])) {
                        $errorRows[] = "Row " . ($index + 2) . ": Invalid status (must be C or P)";
                        continue;
                    }

                    // Convert status
                    $fullStatus = ($status === 'C') ? 'confirm' : 'pending';

                    // Convert date format
                    try {
                        $date = \DateTime::createFromFormat('d/m/Y H:i', $row[2]);
                        if (!$date) {
                            throw new \Exception("Invalid date format");
                        }
                        $formattedDate = $date->format('Y-m-d H:i:s');
                    } catch (\Exception $e) {
                        $errorRows[] = "Row " . ($index + 2) . ": Invalid date format. Use DD/MM/YYYY HH:MM format";
                        continue;
                    }

                    // Generate reservation ID
                    $lastId = DB::table('reservations')
                        ->where('reservationId', 'like', $area . '%')
                        ->orderBy('reservationId', 'desc')
                        ->value('reservationId');

                    $newId = $area . date('Ymd') . sprintf("%03d", 1);
                    if ($lastId) {
                        $sequence = intval(substr($lastId, -3)) + 1;
                        $newId = $area . date('Ymd') . sprintf("%03d", $sequence);
                    }

                    // Insert reservation
                    DB::table('reservations')->insert([
                        'reservationId' => $newId,
                        'customerId' => $row[0],
                        'pax' => $row[1],
                        'reservationDate' => $formattedDate,  // Use converted date
                        'eventType' => $row[3],
                        'rarea' => $area,
                        'rstatus' => $fullStatus,
                        'remark' => $row[6] ?? null,
                        'reservedBy' => 'admin through CSV',
                        'created_at' => now()
                    ]);

                    $successCount++;

                } catch (\Exception $e) {
                    $errorRows[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                }

            }

            $message = "$successCount reservations imported successfully.";
            if (count($errorRows) > 0) {
                $message .= " Errors: " . implode(", ", $errorRows);
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error importing data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getUserReservations($userId)
    {
        try {
            $reservations = DB::table('reservations')
                ->join('customers', 'reservations.customerId', '=', 'customers.customerId')
                ->where('reservations.customerId', $userId)
                ->select([
                    'reservations.reservationId',
                    'reservations.customerId',
                    'customers.name',
                    'reservations.pax',
                    'reservations.reservationDate',
                    'reservations.rarea',
                    'reservations.rstatus'
                ])
                ->orderBy('reservations.reservationDate', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $reservations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch reservations: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getCalendarData()
    {
        try {
            $reservations = DB::table('reservations')
                ->join('customers', 'reservations.customerId', '=', 'customers.customerId')
                ->select(
                    'reservations.reservationId',
                    'reservations.customerId',
                    'customers.name as customer_name',
                    'reservations.pax',
                    DB::raw('DATE_FORMAT(reservationDate, "%Y-%m-%d %H:%i:%s") as rdate'),
                    'reservations.eventType',
                    'reservations.rstatus',
                    'reservations.remark',
                    'reservations.rarea as area'
                )
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $reservations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getNewReservations()
    {
        try {
            $newReservations = DB::table('reservations')
                ->join('customers', 'reservations.customerId', '=', 'customers.customerId')
                ->where('reservations.reservedBy', 'customer')
                ->whereIn('reservations.rstatus', ['firstc', 'secondc', 'thirdc'])
                ->select(
                    'reservations.reservationId',
                    'customers.name as customer_name',
                    'customers.phoneNum as phone_number',
                    'reservations.pax',
                    'reservations.reservationDate',
                    'reservations.rarea as area',
                    'reservations.rstatus'
                )
                ->orderBy('reservations.created_at', 'desc')
                ->get();

            Log::info('New reservations found:', ['count' => $newReservations->count()]);

            return response()->json([
                'success' => true,
                'data' => $newReservations
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch new reservations:', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch new reservations: ' . $e->getMessage()
            ], 500);
        }
    }

    public function markNotificationRead($reservationId)
    {
        try {
            DB::table('reservations')
                ->where('reservationId', $reservationId)
                ->update(['notified' => true]);

            return response()->json([
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read: ' . $e->getMessage()
            ], 500);
        }
    }

    // Add method for customer confirmation
    public function confirmReservation($reservationId)
    {
        try {
            // Update the reservation status to 'confirm' and update timestamps
            DB::table('reservations')
                ->where('reservationId', $reservationId)
                ->update([
                    'rstatus' => 'confirm',
                    'confirmed_at' => now(), // Add confirmed timestamp
                    'status_updated_at' => now(), // Add status update timestamp
                    'updated_at' => now()
                ]);

            // Log the confirmation
            Log::info('Reservation confirmed:', [
                'reservationId' => $reservationId,
                'confirmed_at' => now()
            ]);

            return view('reservations.confirmation-success', [
                'reservationId' => $reservationId
            ]);

        } catch (\Exception $e) {
            Log::error('Reservation confirmation failed:', [
                'reservationId' => $reservationId,
                'error' => $e->getMessage()
            ]);

            return view('reservations.confirmation-error', [
                'message' => 'Failed to confirm reservation'
            ]);
        }
    }

    private function scheduleReminders($reservationId, $reservationDate)
    {
        $reservationDateTime = Carbon::parse($reservationDate);
        $now = Carbon::now();

        // Get reservation and customer details
        $reservation = DB::table('reservations')
            ->join('customers', 'reservations.customerId', '=', 'customers.customerId')
            ->where('reservations.reservationId', $reservationId)
            ->select('reservations.*', 'customers.email', 'customers.name')
            ->first();

        // Schedule reminders with one day intervals
        // First reminder is already sent at creation with 'firstc' status

        // Second reminder after one day
        dispatch(function() use ($reservation) {
            if ($this->isUnconfirmed($reservation->reservationId)) {
                $this->sendReminderEmail($reservation, 'secondc');
            }
        })->delay(now()->addDay());

        // Third reminder after two days
        dispatch(function() use ($reservation) {
            if ($this->isUnconfirmed($reservation->reservationId)) {
                $this->sendReminderEmail($reservation, 'thirdc');
                $this->notifyAdmin($reservation);
            }
        })->delay(now()->addDays(2));
    }

    private function sendReminderEmail($reservation, $status)
    {
        try {
            // Update status first
            DB::table('reservations')
                ->where('reservationId', $reservation->reservationId)
                ->update(['rstatus' => $status]);

            Mail::send('emails.confirmation_' . $status, [
                'reservation' => $reservation
            ], function($message) use ($reservation) {
                $message->to($reservation->email)
                    ->subject('Reservation Confirmation Required');
            });

            Log::info('Reminder email sent', [
                'reservationId' => $reservation->reservationId,
                'status' => $status,
                'sentAt' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send reminder email', [
                'error' => $e->getMessage(),
                'reservationId' => $reservation->reservationId
            ]);
        }
    }

    private function isUnconfirmed($reservationId)
    {
        return DB::table('reservations')
            ->where('reservationId', $reservationId)
            ->whereIn('rstatus', ['firstc', 'secondc'])
            ->exists();
    }

    public function cancelReservation($reservationId)
{
    try {
        DB::table('reservations')
            ->where('reservationId', $reservationId)
            ->update([
                'rstatus' => 'cancel',
                'updated_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Reservation cancelled successfully'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to cancel reservation: ' . $e->getMessage()
        ], 500);
    }
}

}
