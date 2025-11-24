<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\backoffice\TenantProfile\UpdateAboutRequest;
use App\Http\Requests\backoffice\TenantProfile\UpdateGeneralInfoRequest;
use App\Models\TenantProfile;
use App\Services\TenantProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TenantProfileController extends Controller
{
    public function __construct(
        protected TenantProfileService $tenantProfileService
    ) {
    }

    public function aboutIndex() {
        $profile = TenantProfile::where('tenant_id', tenant('id'))->get();
        return view('backoffice.cms.about', compact('profile'));
    }
    public function updateAbout(UpdateAboutRequest $request): RedirectResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $this->tenantProfileService->updateAbout($tenantId, $request->validated());

        return back()->with('success', 'About information updated.');
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
