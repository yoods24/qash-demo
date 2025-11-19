<?php

namespace App\Services\Tax;

use App\Models\TenantNotification;
use App\Models\Tax;

class TaxNotificationService
{
    /**
     * Create a notification for a newly created Tax.
     *
     * @param Tax $tax
     * @return TenantNotification|null
     */
    public function created(Tax $tax): ?TenantNotification
    {
        try {
            return TenantNotification::create([
                'tenant_id'    => tenant('id'),
                'type'         => 'tax',
                'title'        => 'New Tax Created',
                'item_id'      => $tax->id,
                'description'  => 'Tax "' . $tax->name . '" of type "' . $tax->type . '" has been created.',
                'route_name'   => 'backoffice.taxes.index',
                'route_params' => json_encode(['tax' => $tax->id, 'tenant' => tenant('id')]),
            ]);
        } catch (\Throwable $e) {
            // Log but do not block tax creation
            logger()->error('Failed to create tax notification', [
                'tax_id' => $tax->id,
                'error'  => $e->getMessage(),
            ]);

            return null;
        }
    }
}
