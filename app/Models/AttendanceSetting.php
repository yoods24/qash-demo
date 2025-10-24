<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class AttendanceSetting extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'face_recognition_enabled',
        'default_method',
        'default_combined',
        'apply_face_to',
        'geofence',
        'meta',
    ];

    protected $casts = [
        'face_recognition_enabled' => 'boolean',
        'default_combined' => 'boolean',
        'geofence' => 'array',
        'meta' => 'array',
    ];
}
