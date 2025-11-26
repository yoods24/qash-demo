<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TenantProfile;
use Illuminate\Support\Arr;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TenantProfileService
{
    public function updateAbout(string $tenantId, array $data): TenantProfile
    {
        $profile = $this->getProfile($tenantId);
        $profile->fill(Arr::only($data, ['about']));
        $profile->save();

        return $profile->fresh();
    }

    public function updateBrandInfo(string $tenantId, array $data): TenantProfile
    {
        $profile = $this->getProfile($tenantId);
        $profile->fill(Arr::only($data, ['brand_heading', 'brand_slogan']));
        $profile->save();

        return $profile->fresh();
    }

    public function updateGeneralInfo(string $tenantId, array $data): TenantProfile
    {
        $profile = $this->getProfile($tenantId);

        $payload = Arr::only($data, [
            'contact_email',
            'contact_phone',
            'opening_hours',
            'social_links',
            'address',
            'latitude',
            'longitude',
            'logo_url',
        ]);

        if (array_key_exists('opening_hours', $payload)) {
            $payload['opening_hours'] = $this->sanitizeStringArray($payload['opening_hours'] ?? null);
        }

        if (array_key_exists('social_links', $payload)) {
            $payload['social_links'] = $this->sanitizeStringArray($payload['social_links'] ?? null);
        }

        $profile->fill($payload);
        $profile->save();

        return $profile->fresh();
    }

    public function addGalleryPhotos(string $tenantId, array $images): TenantProfile
    {
        $profile = $this->getProfile($tenantId);
        $current = $profile->gallery_photos ?? [];

        foreach ($images as $image) {
            if (! $image instanceof UploadedFile) {
                continue;
            }
            if (count($current) >= 5) {
                break;
            }

            $current[] = $this->storeGalleryImage($tenantId, $image);
        }

        $profile->gallery_photos = array_values($current);
        $profile->save();

        return $profile->fresh();
    }

    public function removeGalleryPhoto(string $tenantId, string $photoPath): TenantProfile
    {
        $profile = $this->getProfile($tenantId);
        $photos = collect($profile->gallery_photos ?? [])
            ->filter(fn ($path) => $path !== $photoPath)
            ->values()
            ->all();

        $profile->gallery_photos = $photos;
        $profile->save();

        Storage::disk('public')->delete($photoPath);

        return $profile->fresh();
    }

    protected function getProfile(string $tenantId): TenantProfile
    {
        return TenantProfile::firstOrCreate(
            ['tenant_id' => $tenantId],
            ['tenant_id' => $tenantId]
        );
    }

    protected function storeGalleryImage(string $tenantId, UploadedFile $image): string
    {
        return $image->store("tenants/{$tenantId}/gallery", 'public');
    }

    protected function sanitizeStringArray(?array $values): ?array
    {
        if (!$values) {
            return null;
        }

        $clean = [];

        foreach ($values as $key => $value) {
            if (is_string($value)) {
                $value = trim($value);
            }

            if ($value === '' || $value === null) {
                continue;
            }

            $clean[$key] = $value;
        }

        return $clean ?: null;
    }
}
