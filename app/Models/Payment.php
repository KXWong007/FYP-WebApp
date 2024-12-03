<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';
    protected $primaryKey = 'paymentId';
    
    protected $fillable = [
        'reservationId',
        'paymentreservationcode',
        'amount',
        'paymentType',
        'paymentDate',
        'paymentMethod',
        'proofPayment'
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservationId', 'reservationId');
    }
}
