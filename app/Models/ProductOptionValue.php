<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class ProductOptionValue extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 
        'product_option_id', 
        'value', 
        'price_adjustment'
    ];
    public function product() {
        return $this->belongsTo(ProductOption::class);
    }
    public function option(){
        return $this->belongsTo(ProductOption::class, 'product_option_id');
    }
}
