<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\backoffice\TenantProfile\UpdateAboutRequest;
use App\Http\Requests\backoffice\TenantProfile\UpdateBrandInfoRequest;
use App\Http\Requests\backoffice\TenantProfile\UpdateGeneralInfoRequest;
use App\Models\TenantProfile;
use App\Services\TenantProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantProfileController extends Controller
{
    public function __construct(
        protected TenantProfileService $tenantProfileService
    ) {
    }

    public function aboutIndex(Request $request): View
    {
        $tenantId = $this->resolveTenantId($request);
        $tenantProfile = TenantProfile::firstOrNew(['tenant_id' => $tenantId]);

        return view('backoffice.cms.about', [
            'tenantProfile' => $tenantProfile,
        ]);
    }

    public function brandInformationIndex(Request $request): View
    {
        $tenantId = $this->resolveTenantId($request);
        $tenantProfile = TenantProfile::firstOrNew(['tenant_id' => $tenantId]);

        return view('backoffice.cms.brand-information', [
            'tenantProfile' => $tenantProfile,
        ]);
    }

    public function updateAbout(UpdateAboutRequest $request): RedirectResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $this->tenantProfileService->updateAbout($tenantId, $request->validated());

        return back()->with('success', 'About information updated.');
    }

    public function updateBrandInfo(UpdateBrandInfoRequest $request): RedirectResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $this->tenantProfileService->updateBrandInfo($tenantId, $request->validated());

        return back()->with('success', 'Brand information updated.');
    }

    public function updateGeneralInfo(UpdateGeneralInfoRequest $request): RedirectResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $this->tenantProfileService->updateGeneralInfo($tenantId, $request->validated());

        return back()->with('success', 'Company information updated.');
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
