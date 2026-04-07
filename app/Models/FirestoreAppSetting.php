<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FirestoreAppSetting extends Model
{
    use HasFactory;

    protected $table = 'firestore_app_settings';

    protected $fillable = [
        'application_package',
        'firebase_project_id',
        'collection_name',
        'document_name',
        'field_name', 
        'credentials_json',
        'credentials_filename',
        'status_flag',
    ];

    protected $casts = [
        'status_flag' => 'boolean',
    ];
}
