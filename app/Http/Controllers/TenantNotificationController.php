<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TenantNotificationController extends Controller
{
    public function index() {
        return view('backoffice.notification.index');
    }
}
