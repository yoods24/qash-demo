<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceSetting;
use App\Services\AttendanceService;

class FaceRecognitionController extends Controller
{
        public function attendance(User $user)
    {
        $user = request()->user();
        return view('backoffice.face-recognition.attendance', ['user' => $user]);
    }

    /**
     * Menampilkan halaman pendaftaran wajah.
     * Catatan: Untuk saat ini hanya memuat tampilan.
     * Form dikirim langsung ke API Flask (http://127.0.0.1:5000/register).
     */
    public function register()
    {
        $user = request()->user();
        return view('backoffice.face-recognition.register', ['user' => $user]);
    }

    /**
     * After Flask recognizes the face, client calls this to record attendance.
     */
    public function confirm(Request $request, AttendanceService $service)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $lat = $request->input('latitude');
        $lng = $request->input('longitude');

        // Determine effective method similar to StaffAttendance
        $settings = AttendanceSetting::firstOrCreate(['tenant_id' => $user->tenant_id]);
        if ($user->attendance_method === 'default') {
            $effective = ($settings->apply_face_to === 'all' && $settings->default_combined)
                ? 'default_combined'
                : ($settings->default_method ?? 'geo');
        } else {
            $effective = $user->attendance_method; // manual|geo|face
        }

        if ($effective === 'manual') {
            return response()->json(['status' => 'error', 'message' => 'Attendance is set to manual by admin.'], 403);
        }

        // Persist a valid method value according to DB enum ['manual','geo','face']
        // For combined mode, we still record as 'face' and rely on geo fields/meta
        $service->clockIn($user, now(), [
            'method' => 'face',
            'lat' => is_numeric($lat) ? (float) $lat : null,
            'lng' => is_numeric($lng) ? (float) $lng : null,
            'device' => $request->userAgent(),
        ]);

        return response()->json(['status' => 'ok', 'message' => 'Attendance recorded']);
    }
}
