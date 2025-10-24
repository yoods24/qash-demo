<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'start_time',
        'end_time',
        'week_off_days',
        'day_rules',
        'breaks',
        'recurring',
        'status',
        'description',
    ];

    protected $casts = [
        'week_off_days' => 'array',
        'day_rules' => 'array',
        'breaks' => 'array',
        'recurring' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
