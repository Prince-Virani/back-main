<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Website extends Model
{
    use HasFactory;
    protected $table = 'websites';
   protected $fillable = ['id','website_name','company_name', 'company_address', 'email', 'contact', 'logo_path','favicon_path','website_theme','status_flag','domain','ga_property_id','aapanel_site_id','website_vertical','website_type'];
}
