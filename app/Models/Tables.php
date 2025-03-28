<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tables extends Model
{
    use HasFactory;
    protected $table = 'tables';
    protected $primaryKey = 'tableNum';
    public $incrementing = false;
    protected $fillable = [
        'tableNum', 
        'capacity', 
        'status', 
        'area'
    ];
}
