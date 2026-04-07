<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdPosition extends Model
{
    use HasFactory;

    protected $table = 'ad_positions';
    protected $fillable = [
     
        'position_name',
        'status_flag',
       
    ];

   
}