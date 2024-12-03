<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Orders extends Model
{
    use HasFactory;

    protected $primaryKey = 'orderId';

    /*protected static function boot()
    {
        parent::boot();

        // Automatically generate the orderId when creating a new order
        static::creating(function ($order) {
            $now = Carbon::now();
            $order->orderId = $now->format('YmdHisv'); // Format: YYYYMMDDHHMMSSMS (MS = milliseconds)
        });
    }*/

    protected $fillable = [
        'customerId',
        'tableNum',
        'orderDate',
        'totalAmount',
        'status',
    ];

    public function customers()
    {
        return $this->belongsTo(Customers::class, 'customerId');
    }

    public function tables()
    {
        return $this->belongsTo(Tables::class, 'tableNum');
    }

    // Define the relationship with OrderItem
    public function orderItems()
    {
        return $this->hasMany(OrderItems::class, 'orderItemId', 'orderItemId');
    }

    public function calculateOrderQuantity()
    {
        return OrderItems::where('orderId', $this->orderId)
            ->sum('quantity');
    }
}
