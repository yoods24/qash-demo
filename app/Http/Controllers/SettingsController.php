<?php

namespace App\Http\Controllers;

use App\Models\TenantInvoiceSettings;
use App\Models\TenantProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('backoffice.settings.general-information');
    }
    public function generalInformationShow(Request $request)
    {
        $tenantId = tenant('id') ?? $request->route('tenant') ?? $request->user()?->tenant_id;
        $tenantProfile = $tenantId
            ? TenantProfile::firstOrNew(['tenant_id' => $tenantId])
            : new TenantProfile();
        return view('backoffice.settings.general-information.company-information-settings', [
            'tenantProfile' => $tenantProfile]);
    }
    public function attendanceShow()
    {
        return view('backoffice.settings.app.attendance-settings');
    }

    public function geolocationShow()
    {
        return view('backoffice.settings.app.geolocation-settings');
    }

    public function invoiceSettingsShow(Request $request)
    {
        $tenantId = tenant('id') ?? $request->route('tenant') ?? $request->user()?->tenant_id;

        if (!$tenantId) {
            abort(400, 'Unable to determine tenant context.');
        }

        $invoiceSettings = TenantInvoiceSettings::firstOrNew(
            ['tenant_id' => $tenantId],
            [
                'invoice_due_days' => 0,
                'invoice_round_off' => false,
                'invoice_round_direction' => 'up',
                'show_company_details' => true,
            ]
        );

        $tenantProfile = TenantProfile::firstOrNew(['tenant_id' => $tenantId]);

        return view('backoffice.settings.app.invoice-settings', [
            'invoiceSettings' => $invoiceSettings,
            'tenantProfile' => $tenantProfile,
        ]);
    }
}
