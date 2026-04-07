<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    use HasFactory;
    protected $table = 'themes'; // Assuming the table name is 'themes'
    protected $fillable = ['id','themename','status_flag'];
}
