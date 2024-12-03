<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staffs extends Model
{
    use HasFactory;

    protected $table = 'staffs';
    protected $primaryKey = 'staffId';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
       'staffId',
       'staffType',
       'name',
       'password',
       'email',
       'gender',
       'religion',
       'race',
       'nric',
       'profilePicture',
       'dateOfBirth',
       'phone',
       'address',
       'status',
       'created at',
       'updated at',
    ]; 
    
    protected $hidden = [
        'password',
        'remember_token',
    ];
}
