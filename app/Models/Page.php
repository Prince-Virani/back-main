<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $table = 'posts';
    protected $fillable = ['id','name', 'content', 'image','categories','paramlink','status_flag','website_id','rendered_content'];
    public function website()
    {
    return $this->belongsTo(Website::class);
    }
}
