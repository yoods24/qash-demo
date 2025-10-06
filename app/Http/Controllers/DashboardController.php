<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function mainDashboard() {
        return view('backoffice.dashboard');
    }
}
