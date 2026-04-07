<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adtxt extends Model
{
    use HasFactory;

    protected $table = 'adstxt';
    protected $fillable = [
        'website_id',
        'content',
    ];

   
    public function website()
    {
        return $this->belongsTo(Website::class);
    }
}