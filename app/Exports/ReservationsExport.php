<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;

class ReservationsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return DB::table('reservations')
            ->join('customers', 'reservations.customerId', '=', 'customers.customerId')
            ->select(
                'reservations.reservationId',
                'reservations.customerId',
                'customers.name as customerName',
                'reservations.pax',
                'reservations.reservationDate',
                'reservations.eventType',
                'reservations.orderId',
                'reservations.paymentId',
                'reservations.rarea',
                'reservations.remark'
            )
            ->get();
    }

    public function headings(): array
    {
        return [
            'Reservation ID',
            'Customer ID',
            'Customer Name',
            'Number of Guests',
            'Reservation Date',
            'Event',
            'Order ID',
            'Payment ID',
            'Area',
            'Remark'
        ];
    }
}

