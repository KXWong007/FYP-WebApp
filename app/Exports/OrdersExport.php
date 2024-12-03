<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;

class OrdersExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return DB::table('orders')
            ->join('staffs', 'orders.servedBy', '=', 'staffs.staffId')
            ->select(
                'orders.orderId',
                'orders.customerId',
                'customers.name as customerName',
                'orders.pax',
                'orders.orderDate',
                'orders.eventType',
                'orders.orderId',
                'orders.paymentId',
                'orders.rarea',
                'orders.remark'
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

