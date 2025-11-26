<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\backoffice\TenantProfile\UpdateAboutRequest;
use App\Http\Requests\backoffice\TenantProfile\UpdateGeneralInfoRequest;
use App\Http\Requests\backoffice\TenantProfile\UpdateGalleryRequest;
use App\Http\Requests\backoffice\TenantProfile\DeleteGalleryPhotoRequest;
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

        return back()->with('message', 'About information updated.');
    }

    public function updateGeneralInfo(UpdateGeneralInfoRequest $request): RedirectResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $this->tenantProfileService->updateGeneralInfo($tenantId, $request->validated());

        return back()->with('message', 'Company information updated.');
    }

    public function galleryIndex()
    {
        $profile = TenantProfile::firstOrCreate(['tenant_id' => tenant('id')]);
        $photos = $profile->gallery_photos ?? [];

        return view('backoffice.cms.gallery', [
            'profile' => $profile,
            'photos' => $photos,
        ]);
    }

    public function storeGallery(UpdateGalleryRequest $request): RedirectResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $existingCount = count(TenantProfile::firstOrCreate(['tenant_id' => $tenantId])->gallery_photos ?? []);
        $files = $request->file('photos', []);
        $allowed = max(0, 5 - $existingCount);
        $files = $allowed > 0 ? array_slice($files, 0, $allowed) : [];

        if (! empty($files)) {
            $this->tenantProfileService->addGalleryPhotos($tenantId, $files);
        }

        return back()->with('message', 'Gallery updated.');
    }

    public function deleteGallery(DeleteGalleryPhotoRequest $request): RedirectResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $photo = $request->validated('photo');
        $this->tenantProfileService->removeGalleryPhoto($tenantId, $photo);

        return back()->with('message', 'Photo removed.');
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
