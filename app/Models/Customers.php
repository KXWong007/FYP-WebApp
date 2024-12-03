<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customers extends Model
{
    use HasFactory;

    protected $table = 'customers';
    protected $primaryKey = 'customerId';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'customerId',
        'customerType',
        'name',
        'password',
        'email',
        'gender',
        'religion',
        'race',
        'nric',
        'profilePicture',
        'dateOfBirth',
        'phoneNum',
        'address',
        'status',
        'created_at',
        'updated_at',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
}
