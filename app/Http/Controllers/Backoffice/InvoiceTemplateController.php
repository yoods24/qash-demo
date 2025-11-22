<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\backoffice\InvoiceSettings\UpdateInvoiceTemplateRequest;
use App\Models\TenantInvoiceTemplate;
use App\Services\InvoiceTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceTemplateController extends Controller
{
    public function __construct(
        protected InvoiceTemplateService $templateService
    ) {
    }

    public function index(Request $request): View
    {
        $tenantId = $this->resolveTenantId($request);
        $templateRecord = TenantInvoiceTemplate::firstOrCreate(
            ['tenant_id' => $tenantId],
            ['selected_template' => 'template_1']
        );

        $templates = [
            'template_1' => [
                'title' => 'Classic Receipt',
                'description' => 'Clean layout with barcode footer',
            ],
            'template_2' => [
                'title' => 'Modern Invoice',
                'description' => 'Two-column header with accent color',
            ],
            'template_3' => [
                'title' => 'Minimal Invoice',
                'description' => 'Bordered table focused on totals',
            ],
        ];

        return view('backoffice.settings.app.invoice-templates', [
            'templates' => $templates,
            'selectedTemplate' => $templateRecord->selected_template,
        ]);
    }

    public function select(UpdateInvoiceTemplateRequest $request): RedirectResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $this->templateService->setTemplateForTenant($tenantId, (string) $request->validated()['template']);

        return back()->with('success', 'Invoice template updated successfully.');
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
