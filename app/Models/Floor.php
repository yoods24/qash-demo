<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Floor extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'area_type',
        'order',
    ];

    public function tables()
    {
        return $this->hasMany(DiningTable::class, 'floor_id');
    }
}

