<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;
    protected $primaryKey = 'dishId';
    protected $table = 'menu';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'dishId',
        'dishName',
        'category',
        'subcategory',
        'cuisine',
        'image',
        'price',
        'availableTime',
        'availableArea',
        'availability',
        'description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'availability' => 'boolean',
        'availableArea' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function orders()
    {
        return $this->belongsTo(Orders::class, 'orderId', 'orderId');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItems::class, 'orderItemId', 'orderItemId');
    }
}
