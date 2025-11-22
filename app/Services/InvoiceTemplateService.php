<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TenantInvoiceTemplate;

class InvoiceTemplateService
{
    public function setTemplateForTenant(string $tenantId, string $template): TenantInvoiceTemplate
    {
        $record = TenantInvoiceTemplate::firstOrCreate(
            ['tenant_id' => $tenantId],
            ['selected_template' => $template]
        );

        if ($record->selected_template !== $template) {
            $record->selected_template = $template;
            $record->save();
            $record->refresh();
        }

        return $record;
    }
}
