<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Forecasting extends Model
{
    use HasFactory;

    protected $table = 'forecasting';

    protected $fillable = [
        'inventoryId',
        'itemName',
        'dailyUsage',
        'measurementUnit',
        'date',
    ];

    // Define relationship with Inventory
    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventoryId', 'inventoryId');
    }
}
