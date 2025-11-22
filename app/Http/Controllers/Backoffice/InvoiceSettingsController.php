<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\backoffice\InvoiceSettings\UpdateInvoiceSettingsRequest;
use App\Services\InvoiceSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvoiceSettingsController extends Controller
{
    public function __construct(
        protected InvoiceSettingsService $invoiceSettingsService
    ) {
    }

    public function update(UpdateInvoiceSettingsRequest $request): RedirectResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $this->invoiceSettingsService->updateSettings($tenantId, $request->validated());

        return back()->with('success', 'Invoice settings saved.');
    }

    protected function resolveTenantId(Request $request): string
    {
        $tenantId = tenant('id')
            ?? $request->route('tenant')
            ?? $request->user()?->tenant_id;

        if (!$tenantId) {
            abort(400, 'Unable to determine tenant context.');
        }

        return (string) $tenantId;
    }
}
