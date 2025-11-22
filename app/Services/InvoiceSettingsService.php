<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TenantInvoiceSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class InvoiceSettingsService
{
    public function updateSettings(string $tenantId, array $data): TenantInvoiceSettings
    {
        $settings = TenantInvoiceSettings::firstOrCreate(
            ['tenant_id' => $tenantId],
            [
                'invoice_due_days' => 0,
                'invoice_round_off' => false,
                'invoice_round_direction' => 'up',
                'show_company_details' => true,
            ]
        );

        $payload = Arr::only($data, [
            'invoice_prefix',
            'invoice_due_days',
            'invoice_round_direction',
            'invoice_header_terms',
            'invoice_footer_terms',
        ]);

        $payload['invoice_due_days'] = isset($payload['invoice_due_days'])
            ? (int) $payload['invoice_due_days']
            : $settings->invoice_due_days;

        $payload['invoice_round_off'] = (bool) ($data['invoice_round_off'] ?? false);
        $payload['show_company_details'] = (bool) ($data['show_company_details'] ?? false);

        if (!isset($payload['invoice_round_direction']) || $payload['invoice_round_direction'] === null) {
            $payload['invoice_round_direction'] = $settings->invoice_round_direction ?? 'up';
        }

        if (isset($data['invoice_logo']) && $data['invoice_logo'] instanceof UploadedFile) {
            $path = $data['invoice_logo']->store('tenant-invoices/logos', 'public');
            $payload['invoice_logo'] = Storage::disk('public')->url($path);
        }

        $settings->fill($payload);

        if ($settings->isDirty()) {
            $settings->save();
            $settings->refresh();
        }

        return $settings;
    }
}
