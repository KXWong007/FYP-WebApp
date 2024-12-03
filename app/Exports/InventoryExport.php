<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;

class InventoryExport implements FromCollection, WithHeadings
{
    /**
     * Fetch inventory data for exporting.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DB::table('inventory')
            ->select(
                'inventoryId',
                'itemName',
                'quantity',
                'minimum',
                'maximum',
                'unitPrice',
                'measurementUnit',
                'created_at',
                'updated_at'
            )
            ->get();
    }

    /**
     * Define the headings for the exported file.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Inventory ID',
            'Item Name',
            'Quantity',
            'Minimum Quantity',
            'Maximum Quantity',
            'Unit Price',
            'Measurement Unit',
            'Created At',
            'Updated At',
        ];
    }
}
