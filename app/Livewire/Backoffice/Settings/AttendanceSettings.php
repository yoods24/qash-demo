<?php

declare(strict_types=1);

namespace App\Livewire\Backoffice\Settings;

use App\Models\AttendanceSetting;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Rule;
use Livewire\Component;

class AttendanceSettings extends Component
{
    public string $view = 'attendance'; // 'attendance' | 'geolocation'
    public string $mode = 'geo'; // 'default_combined'|'geo'|'face'|'manual'
    public bool $face_recognition_enabled = false;
    public string $apply_face_to = 'per_user'; // 'all'|'per_user'

    #[Rule('nullable|numeric')]
    public $geo_lat;
    #[Rule('nullable|numeric')]
    public $geo_lng;
    #[Rule('nullable|integer|min:50|max:5000')]
    public $geo_radius = 200; // meters

    public function mount(?string $view = null): void
    {
        if ($view) {
            $this->view = in_array($view, ['attendance','geolocation'], true) ? $view : 'attendance';
        }
        // Get or create the settings row scoped to current tenant
        $s = AttendanceSetting::firstOrCreate([], [
            'face_recognition_enabled' => false,
            'default_method' => 'geo',
            'default_combined' => false,
            'apply_face_to' => 'per_user',
        ]);

        $this->face_recognition_enabled = (bool) $s->face_recognition_enabled;
        $this->apply_face_to = (string) ($s->apply_face_to ?? 'per_user');

        // Determine UI mode
        if ($s->default_combined) {
            $this->mode = 'default_combined';
        } else {
            $this->mode = (string) $s->default_method;
        }

        $geo = $s->geofence ?? [];
        $this->geo_lat = $geo['lat'] ?? null;
        $this->geo_lng = $geo['lng'] ?? null;
        $this->geo_radius = $geo['radius'] ?? $this->geo_radius;
    }

    public function saveAttendance(): void
    {
        $s = AttendanceSetting::first() ?? new AttendanceSetting();

        // Persist according to selected mode
        $s->default_combined = $this->mode === 'default_combined';
        if ($this->mode === 'default_combined') {
            // Combined requires both geo and face; force-enable face regardless of toggle
            $s->default_method = 'geo'; // used along with face flag
            $s->face_recognition_enabled = true;
        } else {
            // Standalone modes: respect the selected method and the toggle
            $s->default_method = $this->mode; // geo|face|manual
            $s->face_recognition_enabled = (bool) $this->face_recognition_enabled;
        }
        $s->apply_face_to = $this->apply_face_to;
        $s->save();

        session()->flash('success', 'Attendance settings saved.');
    }

    public function saveGeofence(): void
    {
        $this->validate();
        $s = AttendanceSetting::first() ?? new AttendanceSetting();
        $s->geofence = [
            'lat' => $this->geo_lat !== null ? (float) $this->geo_lat : null,
            'lng' => $this->geo_lng !== null ? (float) $this->geo_lng : null,
            'radius' => (int) $this->geo_radius,
        ];
        $s->save();
        session()->flash('success', 'Geolocation saved.');
    }

    public function render()
    {
        if ($this->view === 'geolocation') {
            return view('livewire.backoffice.settings.geolocation-settings');
        }
        return view('livewire.backoffice.settings.attendance-settings');
    }
}
