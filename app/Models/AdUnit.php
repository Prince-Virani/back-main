<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdUnit extends Model
{
    use HasFactory;
    protected $table = 'ad_units';
    protected $fillable = ['id','website_id','adunit_name', 'adunit_id','ad_unit_size','ad_unit_code','ad_unit_type','is_lazy','in_page_position','status_flag'];

   public function website()
    {
        return $this->belongsTo(Website::class);
    }
}
