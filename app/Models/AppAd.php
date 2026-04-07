<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppAd extends Model
{
    use HasFactory; 
   
    protected $table = 'application_ads'; 
    protected $fillable = ['application_id', 'ads','package_name'];
    protected $casts = [
        'ads' => 'array', // Important! So Laravel treats it as array
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}

