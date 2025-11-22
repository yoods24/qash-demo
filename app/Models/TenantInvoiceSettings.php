<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class TenantInvoiceSettings extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'invoice_logo',
        'invoice_prefix',
        'invoice_due_days',
        'invoice_round_off',
        'invoice_round_direction',
        'show_company_details',
        'invoice_header_terms',
        'invoice_footer_terms',
    ];

    protected $casts = [
        'tenant_id' => 'string',
        'invoice_due_days' => 'integer',
        'invoice_round_off' => 'boolean',
        'show_company_details' => 'boolean',
    ];
}
