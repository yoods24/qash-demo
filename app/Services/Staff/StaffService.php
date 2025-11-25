<?php

namespace App\Services\Staff;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class StaffService
{
    public function createStaff(array $data, ?UploadedFile $photo = null, ?string $role = null): User
    {
        $user = new User();
        $this->fillUser($user, $data, true);
        $user->tenant_id = tenant('id');
        $user->is_admin = 0;
        $user->status = 1;
        $user->save();

        if ($photo) {
            $this->replacePhoto($user, $photo);
        } elseif (! $user->getAttribute('profile-image')) {
            $user->setAttribute('profile-image', '');
            $user->save();
        }

        if ($role) {
            $user->assignRole($role);
        }

        return $user;
    }

    public function updateStaff(User $staff, array $data, ?UploadedFile $photo = null, ?string $role = null): User
    {
        $this->fillUser($staff, $data, false);
        $staff->save();

        if ($role) {
            $staff->syncRoles([$role]);
        }

        if ($photo) {
            $this->replacePhoto($staff, $photo);
        }

        return $staff;
    }

    public function updatePhoto(User $staff, UploadedFile $photo): User
    {
        $this->replacePhoto($staff, $photo);
        return $staff;
    }

    protected function fillUser(User $user, array $data, bool $forcePassword): void
    {
        $payload = collect($data)->except(['password_confirmation', 'profile-image', 'role'])->toArray();

        if (! empty($payload['password'])) {
            $payload['password'] = Hash::make($payload['password']);
        } elseif ($forcePassword) {
            $payload['password'] = Hash::make($data['password']);
        } else {
            unset($payload['password']);
        }

        $user->fill($payload);
    }

    protected function replacePhoto(User $staff, UploadedFile $photo): void
    {
        $currentImage = $staff->getAttribute('profile-image');
        if ($currentImage) {
            Storage::disk('public')->delete($currentImage);
        }

        $staff->setAttribute('profile-image', $this->storePhoto($photo));
        $staff->save();
    }

    protected function storePhoto(UploadedFile $photo): string
    {
        return $photo->store('staff', 'public');
    }
}
