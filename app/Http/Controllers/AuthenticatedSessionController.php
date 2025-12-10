<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
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
            'company_code' => ['required', 'string', 'max:50'],
            'password' => 'required'
        ]);

        $submittedCompanyCode = strtoupper(trim($validate['company_code'] ?? ''));

        // Retrieve the user from the database
        $user = User::where('email', $request->email)->first();

        // Pull the tenant code for comparison (case-insensitive)
        $tenant = $user ? Tenant::find($user->tenant_id) : null;
        $tenantCompanyCode = strtoupper((string) ($tenant?->company_code ?? ''));

        // Check if user exists and the password matches the hashed one
        $passwordValid = $user && Hash::check($request->password, $user->password);
        $companyCodeValid = $tenantCompanyCode !== '' && hash_equals($tenantCompanyCode, $submittedCompanyCode);

        if ($passwordValid && $companyCodeValid) {
            // Log the user in
            Auth::login($user);

            // Regenerate session to prevent fixation
            $request->session()->regenerate();
            // Determine tenant from the authenticated user and redirect
            $tenantId = $user->tenant_id;
            ds(redirect()->intended(route('backoffice.dashboard', ['tenant' => $tenantId])));
            
            // Redirect to tenant backoffice dashboard
            return redirect()->intended(route('backoffice.dashboard', ['tenant' => $tenantId]))
            ->with('message', "Successfully logged in. Welcome {$user->fullName()}");
        }

        $errors = [
            'email' => 'The provided credentials do not match our records.'
        ];

        if ($passwordValid && $tenant && $tenantCompanyCode === '') {
            $errors = ['company_code' => 'Company code is not configured for this tenant. Please contact an administrator.'];
        } elseif ($passwordValid && !$companyCodeValid) {
            $errors = ['company_code' => 'Company code does not match our records.'];
        }

        // If credentials don't match, return with an error
        return back()->withErrors($errors)->onlyInput('email', 'company_code');
    }

    public function destroy() {
        Auth::logout();
        return redirect('/login')
        ->with('message', 'You have Successfully logged out!');
    }
}
