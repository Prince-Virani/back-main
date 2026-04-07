<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    use HasFactory;

    protected $table = 'custom_links';

    protected $fillable = [
        'url', 'counter', 'status_flag','package_name'
    ];

    protected $casts = [
        'counter' => 'integer',
        'status_flag' => 'integer',
    ];

    public function scopeActive($q) { return $q->where('status_flag', 1); }
}