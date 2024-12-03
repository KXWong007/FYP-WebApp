<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Exports\PaymentsExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Rap2hpoutre\FastExcel\FastExcel;

class PaymentController extends Controller
{
    public function index()
    {
        $reservations = DB::table('reservations')
            ->leftJoin('payments', 'reservations.reservationId', '=', 'payments.reservationId')
            ->join('customers', 'reservations.customerId', '=', 'customers.customerId')
            ->whereNull('payments.paymentId')
            ->where('reservations.rstatus', 'confirm')
            ->select(
                'reservations.reservationId',
                'customers.customerId',
                'customers.name'
            )
            ->orderBy('reservations.reservationDate', 'desc')
            ->get();
        
        return view('payment', compact('reservations'));
    }

    public function getPayments()
    {
        try {
            $payments = DB::table('payments')
                ->join('reservations', 'payments.reservationId', '=', 'reservations.reservationId')
                ->select([
                    'payments.paymentId',
                    'payments.paymentreservationcode',
                    'reservations.reservationId',
                    'payments.amount',
                    'payments.paymentType',
                    'payments.paymentDate',
                    'payments.paymentMethod'
                ])
                ->orderBy('payments.paymentDate', 'desc')
                ->get();

            return response()->json([
                'data' => $payments,
                'success' => true
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching payments: ' . $e->getMessage());
            return response()->json([
                'data' => [],
                'success' => false,
                'message' => 'Error fetching payments: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reservationId' => 'required|exists:reservations,reservationId',
            'paymentreservationcode' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'paymentType' => 'required|string',
            'paymentDate' => 'required|date',
            'paymentMethod' => 'required|string',
            'proofPayment' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        try {
            DB::beginTransaction();

            $paymentData = [
                'reservationId' => $request->reservationId,
                'paymentreservationcode' => $request->paymentreservationcode,
                'amount' => $request->amount,
                'paymentType' => $request->paymentType,
                'paymentDate' => $request->paymentDate,
                'paymentMethod' => $request->paymentMethod,
            ];

            if ($request->hasFile('proofPayment')) {
                $file = $request->file('proofPayment');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('payment_proofs', $fileName, 'public');
                $paymentData['proofPayment'] = $filePath;
            }

            $payment = Payment::create($paymentData);

            Reservation::where('reservationId', $request->reservationId)
                ->update(['paymentId' => $request->paymentreservationcode]);

            DB::commit();
            return response()->json(['success' => 'Payment added successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Payment creation error: ' . $e->getMessage());
            return response()->json(['error' => 'Error adding payment: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $payment = Payment::findOrFail($id);
            
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0',
                'paymentType' => 'required|in:deposit,full',
                'paymentMethod' => 'required|in:online,cash',
                'paymentDate' => 'required|date',
                'proofPayment' => 'nullable|image|max:2048'
            ]);

            if ($request->hasFile('proofPayment')) {
                // Delete old image if exists
                if ($payment->proofPayment) {
                    Storage::disk('public')->delete($payment->proofPayment);
                }

                $file = $request->file('proofPayment');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('payment_proofs', $fileName, 'public');
                $validated['proofPayment'] = $filePath;
            }

            $payment->update($validated);
            
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $payment = Payment::findOrFail($id);
            
            // First, update the associated reservation's paymentId to null
            Reservation::where('reservationId', $payment->reservationId)
                ->update(['paymentId' => null]);

            // Delete image file if exists
            if ($payment->proofPayment) {
                Storage::disk('public')->delete($payment->proofPayment);
            }

            // Delete the payment record
            $payment->delete();

            DB::commit();
            return response()->json(['success' => 'Payment deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error deleting payment: ' . $e->getMessage()], 500);
        }
    }

    public function export($type)
    {
        try {
            $payments = DB::table('payments')
                ->join('reservations', 'payments.reservationId', '=', 'reservations.reservationId')
                ->join('customers', 'reservations.customerId', '=', 'customers.customerId')
                ->select([
                    'payments.paymentId',
                    'payments.paymentreservationcode',
                    'payments.reservationId',
                    'customers.customerId',
                    'customers.name as customerName',
                    'payments.amount',
                    'payments.paymentType',
                    'payments.paymentDate',
                    'payments.paymentMethod'
                ])
                ->orderBy('payments.paymentDate', 'desc')
                ->get();

            $filename = 'payments_' . date('Y-m-d');

            // Transform the data
            $mappedPayments = $payments->map(function ($payment) {
                return [
                    'Payment ID' => $payment->paymentId,
                    'Reservation Code' => $payment->paymentreservationcode,
                    'Reservation ID' => $payment->reservationId,
                    'Customer ID' => $payment->customerId,
                    'Customer Name' => $payment->customerName,
                    'Amount (RM)' => number_format($payment->amount, 2),
                    'Payment Type' => ucfirst($payment->paymentType),
                    'Payment Date' => date('Y-m-d H:i:s', strtotime($payment->paymentDate)),
                    'Payment Method' => ucfirst($payment->paymentMethod)
                ];
            });

            switch ($type) {
                case 'xlsx':
                    return (new FastExcel($mappedPayments))->download($filename . '.xlsx');
                
                case 'csv':
                    return (new FastExcel($mappedPayments))->download($filename . '.csv');
                
                case 'pdf':
                    $pdf = PDF::loadView('exports.payments-pdf', [
                        'payments' => $payments
                    ]);
                    return $pdf->download($filename . '.pdf');
                
                default:
                    return redirect()->back()->with('error', 'Invalid export type');
            }
        } catch (\Exception $e) {
            \Log::error('Export error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $payment = Payment::findOrFail($id);
            return response()->json(['payment' => $payment]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Payment not found'], 404);
        }
    }

    public function getData()
    {
        try {
            $payments = DB::table('payments')
                ->join('reservations', 'payments.reservationId', '=', 'reservations.reservationId')
                ->join('customers', 'reservations.customerId', '=', 'customers.customerId')
                ->select([
                    'payments.paymentId',
                    'payments.paymentreservationcode',
                    'payments.reservationId',
                    'reservations.customerId',
                    'customers.name',
                    'payments.amount',
                    'payments.paymentType',
                    'payments.paymentDate',
                    'payments.paymentMethod',
                    'payments.proofPayment'
                ])
                ->orderBy('payments.paymentDate', 'desc')
                ->get();

            return response()->json([
                'data' => $payments,
                'success' => true
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching payments: ' . $e->getMessage());
            return response()->json([
                'data' => [],
                'success' => false,
                'message' => 'Error fetching payments: ' . $e->getMessage()
            ], 500);
        }
    }

} 