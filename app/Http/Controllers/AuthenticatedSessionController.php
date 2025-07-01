<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthenticatedSessionController extends Controller
{
    public function create() {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        // Validate the incoming request
        $validate = $request->validate([
            'email' => ['required', 'email', 'max:50'],
            'password' => 'required'
        ]);

        // Retrieve the user from the database
        $user = User::where('email', $request->email)->first();

        // Check if user exists and the password matches the hashed one
        if ($user && Hash::check($request->password, $user->password)) {
            // Log the user in
            Auth::login($user);

            // Regenerate session to prevent fixation
            $request->session()->regenerate();

            // Redirect to the intended page
            return redirect()->intended('/backoffice')->with('message', 'Logged In as ' . $user->name);
        }

        // If credentials don't match, return with an error
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.'
        ])->onlyInput('email');
    }

    public function destroy() {
        Auth::logout();
        return redirect('/login')->with('message', 'You have been logged out!');
    }
}
