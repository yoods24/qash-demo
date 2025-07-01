<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function edit(User $user) {
        return view('backoffice.user.edit', ['user' => $user]);
    }

    public function passwordUpdate(User $user) {

    }

    public function notificationUpdate(User $user) {

    }
}
