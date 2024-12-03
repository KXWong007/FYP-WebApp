<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventory'; // Ensure this matches your database table name
    protected $primaryKey = 'inventoryId';
    public $incrementing = false; // Since your primary key is a string
    protected $keyType = 'string'; // Define the key type as string

    protected $fillable = [
        'inventoryId',
        'itemName',
        'quantity',
        'minimum',
        'maximum',
        'unitPrice',
        'measurementUnit',
    ];

    /**
     * Define a relationship with the Forecasting model.
     * Assuming each inventory item has many forecasting records.
     */
    public function forecasting()
    {
        return $this->hasMany(Forecasting::class, 'inventoryId', 'inventoryId'); // Adjust the column names if necessary
    }
}
