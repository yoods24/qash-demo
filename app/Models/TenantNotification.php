<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantNotification extends Model
{
    protected $fillable = [
        'tenant_id',
        'type',
        'description',
        'title',
        'is_read',
        'item_id',
        'route_name',
        'route_params'
    ];

    protected $casts = [
        'route_params' => 'array',
        'is_read' => 'boolean',
    ];
}
