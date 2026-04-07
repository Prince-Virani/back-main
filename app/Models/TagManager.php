<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagManager extends Model
{
    use HasFactory;

    protected $table = 'tag_manager';
    protected $fillable = [
        'id',
        'website_id',
        'content',
        'status_flag',
       
    ];

   
    public function website()
    {
        return $this->belongsTo(Website::class);
    }
}