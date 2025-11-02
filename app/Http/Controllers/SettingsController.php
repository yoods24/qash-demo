<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index () {
         return view ('backoffice.settings.index');
    }
    public function attendanceShow() {
        return view ('backoffice.settings.app.attendance-settings');
    }
    public function geolocationShow() {
        return view ('backoffice.settings.app.geolocation-settings');
    }
}
