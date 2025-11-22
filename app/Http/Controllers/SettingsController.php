<?php

namespace App\Http\Controllers;

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

}
