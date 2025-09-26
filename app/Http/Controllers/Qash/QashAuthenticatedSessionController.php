<?php

declare(strict_types=1);

namespace App\Http\Controllers\Qash;

use App\Http\Controllers\Controller;
use App\Models\QashUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class QashAuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('qash.auth.login');
    }

    public function store(Request $request)
    {
        $validate = $request->validate([
            'email' => ['required', 'email', 'max:50'],
            'password' => ['required'],
        ]);

        $user = QashUser::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            Auth::guard('qash')->login($user);
            $request->session()->regenerate();
            return redirect()->intended('/qash');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials for Qash admin.',
        ])->onlyInput('email');
    }

    public function destroy(Request $request)
    {
        Auth::guard('qash')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/qash/login')->with('message', 'Logged out');
    }
}

