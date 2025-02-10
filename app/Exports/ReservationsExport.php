<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;

class ReservationsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        // Get start and end of current week
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        return DB::table('reservations')
            ->join('customers', 'reservations.customerId', '=', 'customers.customerId')
            ->whereBetween('reservations.reservationDate', [$startOfWeek, $endOfWeek])
            ->select(
                'reservations.reservationId',
                'reservations.customerId',
                'customers.name as customerName',
                'reservations.pax',
                'reservations.reservationDate',
                'reservations.eventType',
                'reservations.orderId',
                DB::raw("CASE 
                    WHEN reservations.rarea = 'W' THEN 'Rajah Room'
                    WHEN reservations.rarea = 'C' THEN 'Hornbill Restaurant'
                    ELSE reservations.rarea 
                END as rarea"),
                'reservations.tableNum',
                'reservations.rstatus',
                'reservations.remark'
            )
            ->orderBy('reservations.reservationDate', 'asc')
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
            'Area',
            'Table Number',
            'Status',
            'Remark'
        ];
    }
}

