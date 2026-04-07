<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;
    protected $table = 'applications';
    protected $fillable = [
        'application_name',
        'package_name',
        'title',
        'rating',
        'installs',
        'play_store',
        'contains_ads',
        'icon_url',
        'app_size',
        'app_version',
        'first_released',
        'last_updated',
        'privacy_policy_url',
        'api_keys'
    ];

    public function ad()
{
    return $this->hasOne(AppAd::class);
}
}
