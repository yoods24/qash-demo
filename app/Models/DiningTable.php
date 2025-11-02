<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiningTable extends Model
{
    use HasFactory;

    protected $table = 'dining_tables';

    protected $fillable = [
        'tenant_id', 'floor_id',
        'label',
        'status',
        'shape',
        'x', 'y', 'w', 'h',
        'capacity',
        'color',
        'qr_code',
    ];

    protected $casts = [
        'x' => 'integer',
        'y' => 'integer',
        'w' => 'integer',
        'h' => 'integer',
        'capacity' => 'integer',
    ];

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }
}
