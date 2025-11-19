<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\Qash\TenantController;
use App\Http\Controllers\Qash\QashAuthenticatedSessionController;


Route::get('/', function () {
    return view('customer.home');
})->name('home');


// Qash central auth (separate guard)
Route::prefix('qash')->name('qash.')->group(function () {
    Route::get('/login', [QashAuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [QashAuthenticatedSessionController::class, 'store'])->name('auth.store');
    Route::post('/logout', [QashAuthenticatedSessionController::class, 'destroy'])->name('auth.logout');
});

// Qash central admin (protected by qash guard)
Route::middleware('auth:qash')->prefix('qash')->name('qash.')->group(function () {
    Route::get('/', [TenantController::class, 'index'])->name('dashboard');
    Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
});


// Tenant customer routes moved under routes/tenant.php (path-based)


// Authentication 
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('auth.store');
Route::post('/logout/{user}', [AuthenticatedSessionController::class, 'destroy'])->name('auth.logout');


// Tenant customer routes moved under routes/tenant.php (path-based)
// Backoffice routes moved under routes/tenant.php (path-based)

Route::post('/payment/webhook/xendit', [PaymentWebhookController::class, 'handle'])
    ->name('payment.webhook');
