<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class CustomerDetail extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'gender',
        'dining_table_id',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function diningTable()
    {
        return $this->belongsTo(DiningTable::class);
    }

}
