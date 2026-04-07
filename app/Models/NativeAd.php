<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NativeAd extends Model
{
    protected $table = 'app_native_ads';

    protected $fillable = [
        'packagename',
        'calltoactionlink',
        'mediaurl',
        'description',
        'title',
        'icon',
        'buttontext',
        'status_flag',
    ];

    public $timestamps = true;
}
