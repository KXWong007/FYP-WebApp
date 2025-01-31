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
                ? '<span style="color:green"> Customer ID exists.</span>'
                : '<span style="color:red"> None record.</span>'
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
                // Get area name based on rarea code
                $areaMap = [
                    'C' => 'Hornbill Restaurant',
                    'W' => 'Rajah Room'
                ];
                $areaName = $areaMap[$request->rarea] ?? null;
                
                // Find available table
                $availableTable = DB::table('tables')
                    ->where('capacity', '>=', $request->pax)
                    ->where('capacity', '<=', $request->pax + 1)
                    ->where('status', 'Available')
                    ->where('area', $areaName)
                    ->whereNotExists(function ($query) use ($request) {
                        $query->select(DB::raw(1))
                            ->from('reservations')
                            ->whereColumn('tables.tableNum', 'reservations.tableNum')
                            ->where('reservationDate', '>=', $request->reservationDate)
                            ->where('reservationDate', '<=', Carbon::parse($request->reservationDate)->addHours(2))
                            ->where('rstatus', 'confirm');
                    })
                    ->first();

                // Set status and tableNum based on availability
                $status = $availableTable ? 'confirm' : 'waitinglist';
                $tableNum = $availableTable ? $availableTable->tableNum : null;

                $reservation = [
                    'reservationId' => $request->reservationId,
                    'customerId' => $request->customerId,
                    'pax' => $request->pax,
                    'reservationDate' => $request->reservationDate,
                    'rarea' => $request->rarea,
                    'reservedBy' => 'customer',
                    'rstatus' => $status,
                    'tableNum' => $tableNum,
                    'created_at' => now(),
                ];
            
                DB::table('reservations')->insert($reservation);

                // If table was found, update its status
                if ($availableTable) {
                    DB::table('tables')
                        ->where('tableNum', $tableNum)
                        ->update(['status' => 'Reserved']);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Reservation created successfully',
                    'status' => $status
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

                // Check table availability and get specific table
                $areaMap = [
                    'C' => 'Hornbill Restaurant',
                    'W' => 'Rajah Room'
                ];
                
                $areaName = $areaMap[$validated['rarea']] ?? null;
                $reservationDate = Carbon::parse($validated['reservation_date']);

                // Find an available table
                $availableTable = DB::table('tables')
                    ->where('capacity', '>=', $validated['pax'])
                    ->where('capacity', '<=', $validated['pax'] + 1)
                    ->where('status', 'Available')
                    ->where('area', $areaName)
                    ->whereNotExists(function ($query) use ($reservationDate) {
                        $query->select(DB::raw(1))
                            ->from('reservations')
                            ->whereColumn('tables.tableNum', 'reservations.tableNum')
                            ->where('reservationDate', '>=', $reservationDate)
                            ->where('reservationDate', '<=', $reservationDate->copy()->addHours(2))
                            ->where('rstatus', 'confirm');
                    })
                    ->first();

                // Set status and tableNum based on availability
                $status = $availableTable ? 'confirm' : 'waitinglist';
                $tableNum = $availableTable ? $availableTable->tableNum : null;

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
                    'rstatus' => $status,
                    'tableNum' => $tableNum,
                    'created_at' => now()
                ]);

                // If table was found, update its status
                if ($availableTable) {
                    DB::table('tables')
                        ->where('tableNum', $tableNum)
                        ->update(['status' => 'Reserved']);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Reservation created successfully',
                    'status' => $status
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

            // Get the reservation and its current status before update
            $reservation = DB::table('reservations')
                ->where('reservationId', $id)
                ->first();

            $oldStatus = $reservation->rstatus;
            $oldTableNum = $reservation->tableNum;

            // Check if new table is needed based on pax change
            if ($request->pax != $reservation->pax) {
                $areaMap = [
                    'C' => 'Hornbill Restaurant',
                    'W' => 'Rajah Room'
                ];
                
                $areaName = $areaMap[$reservation->rarea] ?? null;
                
                // Find new suitable table
                $availableTable = DB::table('tables')
                    ->where('capacity', '>=', $request->pax)
                    ->where('capacity', '<=', $request->pax + 1)
                    ->where('status', 'Available')
                    ->where('area', $areaName)
                    ->first();

                // Update old table status if exists
                if ($oldTableNum) {
                    DB::table('tables')
                        ->where('tableNum', $oldTableNum)
                        ->update(['status' => 'Available']);
                }

                // Update reservation with new table or set to waiting list
                if ($availableTable) {
                    $validated['tableNum'] = $availableTable->tableNum;
                    $validated['rstatus'] = 'confirm';
                    // Update new table status
                    DB::table('tables')
                        ->where('tableNum', $availableTable->tableNum)
                        ->update(['status' => 'Reserved']);
                } else {
                    $validated['tableNum'] = null; // Explicitly set tableNum to null
                    $validated['rstatus'] = 'waitinglist';
                }
            } else {
                // If pax hasn't changed, keep the existing tableNum
                $validated['tableNum'] = $oldTableNum;
            }

            // Update the reservation
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
                    'tableNum' => $validated['tableNum'], // Use the validated tableNum
                    'updated_at' => now()
                ]);

            // If status changed to completed or cancelled and there's a table assigned
            if (($validated['rstatus'] === 'completed' || $validated['rstatus'] === 'cancel') && $oldTableNum) {
                // Update table status to Available
                DB::table('tables')
                    ->where('tableNum', $oldTableNum)
                    ->update(['status' => 'Available']);

                // Process waiting list after table becomes available
                $this->processWaitingList();
            }

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
            // Get the reservation details before deletion to check for assigned table
            $reservation = DB::table('reservations')
                ->where('reservationId', $id)
                ->first();

            if (!$reservation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservation not found'
                ], 404);
            }

            // If there's an assigned table, update its status to Available
            if ($reservation->tableNum) {
                DB::table('tables')
                    ->where('tableNum', $reservation->tableNum)
                    ->update(['status' => 'Available']);
            }

            // Delete the reservation
            DB::table('reservations')
                ->where('reservationId', $id)
                ->delete();

            // Process waiting list after table becomes available
            if ($reservation->tableNum) {
                $this->processWaitingList();
            }

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
                ->whereIn('reservations.rstatus', ['confirm'])
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

    public function updateReservation(Request $request, $reservationId)
    {
        try {
            $reservation = Reservation::where('reservationId', $reservationId)->first();
            
            if (!$reservation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservation not found'
                ], 404);
            }

            // Store the old status and tableNum before update
            $oldStatus = $reservation->rstatus;
            $tableNum = $reservation->tableNum;

            // Update reservation
            $reservation->update($request->all());

            // If status changed to 'completed' or 'cancel', update table status to available
            if (($oldStatus !== 'completed' && $reservation->rstatus === 'completed') || 
                ($oldStatus !== 'cancel' && $reservation->rstatus === 'cancel')) {
                if ($tableNum) {
                    DB::table('tables')
                        ->where('tableNum', $tableNum)
                        ->update(['status' => 'Available']);
                    
                    // Process waiting list after table becomes available
                    $this->processWaitingList();
                }
            }

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

    public function cancelReservation($reservationId)
    {
        try {
            $reservation = Reservation::where('reservationId', $reservationId)->first();
            
            if (!$reservation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservation not found'
                ], 404);
            }

            // Get the tableNum before updating status
            $tableNum = $reservation->tableNum;

            // Update reservation status and clear tableNum
            $reservation->update([
                'rstatus' => 'cancel',
                'updated_at' => now()
            ]);

            // If there was a table assigned, update its status to available
            if ($tableNum) {
                DB::table('tables')
                    ->where('tableNum', $tableNum)
                    ->update(['status' => 'Available']);
            }

            // Process waiting list after cancellation
            $this->processWaitingList();

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

    public function completeReservation($reservationId)
    {
        try {
            $reservation = Reservation::where('reservationId', $reservationId)->first();
            
            if (!$reservation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservation not found'
                ], 404);
            }

            // Get the tableNum before updating status
            $tableNum = $reservation->tableNum;

            // Update reservation status and clear tableNum
            $reservation->update([
                'rstatus' => 'completed',
                'updated_at' => now()
            ]);

            // If there was a table assigned, update its status to available
            if ($tableNum) {
                DB::table('tables')
                    ->where('tableNum', $tableNum)
                    ->update(['status' => 'Available']);
                
                // Process waiting list after table becomes available
                $this->processWaitingList();
            }

            return response()->json([
                'success' => true,
                'message' => 'Reservation marked as completed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete reservation: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAvailableTables()
    {
        try {
            // Get total number of tables
            $totalTables = DB::table('tables')
                ->where('status', 'Available')
                ->count();

            // Get number of reserved tables for current time
            $reservedTables = DB::table('reservations')
                ->where('reservationDate', '>=', now())
                ->where('reservationDate', '<=', now()->addHours(2)) // Consider reservations in next 2 hours
                ->where('rstatus', 'confirm')
                ->count();

            $availableTables = $totalTables - $reservedTables;
            $availableTables = max(0, $availableTables); // Ensure we don't return negative numbers

            return response()->json([
                'success' => true,
                'count' => $availableTables
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting available tables: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkTableAvailability(Request $request)
    {
        try {
            $pax = $request->input('pax');
            $reservationDate = Carbon::parse($request->input('reservation_date'));
            $areaCode = $request->input('rarea');
            
            // Map area codes to full names
            $areaMap = [
                'C' => 'Hornbill Restaurant',
                'W' => 'Rajah Room'
            ];
            
            $areaName = $areaMap[$areaCode] ?? null;
            
            if (!$areaName) {
                throw new \Exception('Invalid area code');
            }

            // Get total number of tables that can accommodate the group
            $availableTables = DB::table('tables')
                ->where('capacity', '>=', $pax)
                ->where('capacity', '<=', $pax + 1)
                ->where('status', 'Available')
                ->where('area', $areaName)
                ->count();

            // Get number of existing reservations for this time slot
            $existingReservations = DB::table('reservations')
                ->where('reservationDate', '>=', $reservationDate)
                ->where('reservationDate', '<=', $reservationDate->copy()->addHours(2))
                ->where('rstatus', 'confirm')
                ->where('rarea', $areaCode)
                ->count();

            // Calculate if tables are available
            $tablesAvailable = ($availableTables - $existingReservations) > 0;

            return response()->json([
                'available' => $tablesAvailable,
                'message' => $tablesAvailable ? 'Tables available' : 'Waiting list'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking table availability: ' . $e->getMessage()
            ], 500);
        }
    }

    public function processWaitingList()
    {
        try {
            \Log::info('Starting to process waiting list');
            
            // Get all waiting list reservations ordered by creation date
            $waitingList = Reservation::where('rstatus', 'waitinglist')
                ->orderBy('created_at')
                ->get();

            \Log::info('Found waiting list reservations:', ['count' => $waitingList->count()]);

            foreach ($waitingList as $reservation) {
                $areaMap = [
                    'C' => 'Hornbill Restaurant',
                    'W' => 'Rajah Room'
                ];
                
                $areaName = $areaMap[$reservation->rarea] ?? null;
                
                \Log::info('Processing reservation:', [
                    'reservationId' => $reservation->reservationId,
                    'pax' => $reservation->pax,
                    'area' => $areaName
                ]);

                // Check if there's an available table for this reservation
                $availableTable = DB::table('tables')
                    ->where('capacity', '>=', $reservation->pax)
                    ->where('capacity', '<=', $reservation->pax + 1)
                    ->where('status', 'Available')
                    ->where('area', $areaName)  // Added area check
                    ->whereNotExists(function ($query) use ($reservation) {
                        $query->select(DB::raw(1))
                            ->from('reservations')
                            ->whereColumn('tables.tableNum', 'reservations.tableNum')
                            ->where('reservationDate', '>=', $reservation->reservationDate)
                            ->where('reservationDate', '<=', Carbon::parse($reservation->reservationDate)->addHours(2))
                            ->where('rstatus', 'confirm');
                    })
                    ->first();

                \Log::info('Available table check result:', ['table' => $availableTable]);

                if ($availableTable) {
                    // Update reservation status to confirm
                    $reservation->update([
                        'rstatus' => 'confirm',
                        'tableNum' => $availableTable->tableNum
                    ]);

                    // Update table status to Reserved
                    DB::table('tables')
                        ->where('tableNum', $availableTable->tableNum)
                        ->update(['status' => 'Reserved']);

                    \Log::info('Updated reservation and table:', [
                        'reservationId' => $reservation->reservationId,
                        'tableNum' => $availableTable->tableNum,
                        'newStatus' => 'Reserved'
                    ]);
                }
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Error processing waiting list: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error processing waiting list: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkEditTableAvailability(Request $request)
    {
        try {
            $pax = $request->input('pax');
            $reservationDate = Carbon::parse($request->input('reservation_date'));
            $currentTable = $request->input('current_table');
            $reservationId = $request->input('reservation_id');
            
            // Get the area code from the current reservation
            $reservation = DB::table('reservations')
                ->where('reservationId', $reservationId)
                ->first();
                
            $areaCode = $reservation->rarea;
            
            \Log::info('Checking table availability:', [
                'pax' => $pax,
                'area' => $areaCode,
                'date' => $reservationDate,
                'currentTable' => $currentTable,
                'reservationId' => $reservationId
            ]);

            // Find suitable table
            $availableTable = DB::table('tables')
                ->where('capacity', '>=', $pax)
                ->where('capacity', '<=', $pax + 1)
                ->where('status', 'Available') // Only check for Available tables
                ->where('area', $areaCode === 'C' ? 'Hornbill Restaurant' : 'Rajah Room')
                ->whereNotExists(function ($query) use ($reservationDate, $reservationId) {
                    $query->select(DB::raw(1))
                        ->from('reservations')
                        ->whereColumn('tables.tableNum', 'reservations.tableNum')
                        ->where('reservationId', '!=', $reservationId)
                        ->where('reservationDate', '>=', $reservationDate)
                        ->where('reservationDate', '<=', $reservationDate->copy()->addHours(2))
                        ->whereIn('rstatus', ['confirm', 'pending']);
                })
                ->orderBy('capacity') // Get the smallest suitable table first
                ->first();

            \Log::info('Available table found:', [
                'table' => $availableTable
            ]);

            if ($availableTable) {
                return response()->json([
                    'available' => true,
                    'message' => 'Table available',
                    'newTableNum' => $availableTable->tableNum,
                    'currentStatus' => 'Available'  // Add status information
                ]);
            } else {
                return response()->json([
                    'available' => false,
                    'message' => 'Waiting list',
                    'currentStatus' => 'Not Available'
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error in checkEditTableAvailability:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error checking table availability: ' . $e->getMessage()
            ], 500);
        }
    }

}
