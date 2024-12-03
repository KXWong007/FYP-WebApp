<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItems extends Model
{
    use HasFactory;

    protected $primaryKey = 'orderItemId';
    protected $table = 'orderItems';
    protected $fillable = [
        'orderId',
        'dishId',
        'servedBy',
        'quantity',
        'status',
        'remark'
    ];

    public function getTotalPrice()
    {
        return $this->quantity * $this->menu->price;
    }

    public function orders()
    {
        return $this->belongsTo(Orders::class, 'orderId', 'orderId');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'dishId', 'dishId');
    }

    public function staffs()
    {
        return $this->belongsTo(Staffs::class, 'servedBy', 'staffId'); 
    }
}
