<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoogleAdSetting extends Model
{
    use HasFactory;

    protected $table = 'google_ad_settings';
    protected $fillable = [
        'website_id',
        'network_code',
        'google_adsense_name',
        'adx_name',
        'status',
        'credentials_path',
        'ga_ad_unit_run_id',
        'ga_order_id',
        'ga_advertiser_id',
        'ga_custom_targeting_key_id',
        'ga_web_property',
        'ga4_account_id',

    ];
    public function website()
    {
        return $this->belongsTo(Website::class);
    }
}
