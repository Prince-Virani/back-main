<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class APIKey extends Model
{
    use HasFactory;
    protected $table = 'api_keys'; // Assuming the table name is 'themes'
    protected $fillable = ['id','package_name','api_key','status_flag'];
}
