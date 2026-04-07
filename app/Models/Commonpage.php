<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commonpage extends Model
{
    use HasFactory;

    protected $table = 'commonpages';
    protected $fillable = ['id','page_name', 'content', 'website_id','status_flag','slug'];
    
    public function website()
    {
    return $this->belongsTo(Website::class);
    }
}
