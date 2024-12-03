<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;
    protected $table = 'reservations';
    protected $primaryKey = 'reservationId';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'reservationId',
        'orderId', // Used by admin
        'customerId',
        'paymentId', // Used by admin
        'reservationDate',
        'pax',
        'eventType', // Used by admin
        'rarea',
        'remark', // Used by admin
        'reserveBy',
        'rstatus'
    ];

}
