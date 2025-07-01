<?php

use App\Models\Career;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AuthenticatedSessionController;

Route::get('/', function () {
    return view('customer.home');
})->name('home');

Route::get('/menu', function () {
    $products = Product::all();
    return view('customer.menu.index', ['products' => $products]);
});


// Authentication 
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('auth.login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('auth.store');
Route::post('/logout/{user}', [AuthenticatedSessionController::class, 'destroy'])->name('auth.logout');




//career
Route::get('/career', [CareerController::class, 'indexCustomer']);





Route::prefix('backoffice')->middleware('auth')->group(function() {
    Route::get('/', function() {
        return view('backoffice.dashboard');
    });
//career
    Route::get('/career/create', [CareerController::class, 'create'])->name('backoffice.career.create');
    Route::get('/career/{career}/edit', [CareerController::class, 'edit'])->name('backoffice.career.edit');
    Route::post('/career/create/store', [CareerController::class, 'store'])->name('backoffice.career.store')
        ->middleware('permission:career_create');;
    Route::delete('/career/{career}/delete', [CareerController::class, 'destroy'])->name('backoffice.career.destroy')
        ->middleware('permission:career_delete');
    Route::get('/career', [CareerController::class, 'indexBackoffice'])->name('backoffice.careers.index');

//staffs
    Route::get('/staff', [StaffController::class, 'index'])->name('backoffice.staff.index');
    Route::post('/staff/store', [StaffController::class, 'storeStaff'])->name('backoffice.staff.store');
    Route::get('/roles', [StaffController::class, 'indexRoles'])->name('backoffice.roles.index');
    Route::delete('/staff/{staff}/delete', [StaffController::class, 'destroy'])->name('backoffice.staff.destroy');


//role
    Route::post('/roles/create', [StaffController::class, 'storeRole'])->name('backoffice.role.store');
    Route::delete('/roles/{role}/delete', [StaffController::class, 'destroyRole'])->name('backoffice.role.destroy');
    Route::get('/roles/{role}/permissions', [StaffController::class, 'indexPermission'])->name('backoffice.permission.index');
    Route::put('/roles/{role}/permissions/update', [StaffController::class, 'updatePermission'])->name('backoffice.permission.update');

// Category
    Route::get('/category', [CategoryController::class, 'index'])->name('backoffice.category.index');
    Route::post('/store', [CategoryController::class, 'store'])->name('backoffice.category.store');
    Route::delete('/category/{category}/delete', [CategoryController::class, 'destroy'])->name('backoffice.category.destroy');

// Product
    Route::get('/product', [ProductController::class, 'index'])->name('backoffice.product.index');
    Route::get('/product/create', [ProductController::class, 'create'])->name('backoffice.product.create');
    Route::post('/product/store', [ProductController::class, 'store'])->name('backoffice.product.store');
    Route::get('/product/{product}/edit', [ProductController::class, 'edit'])->name('backoffice.product.edit');


// User Manipulate
    Route::get('/user/{user}/edit', [UserController::class, 'edit'])->name('backoffice.user.edit');
    Route::get('/user/{user}/profile-update', [UserController::class, 'profileUpdate'])->name('backoffice.profile.update');
    Route::put('/user/{user}/update-password', [UserController::class, 'passwordUpdate'])->name('backoffice.profile.password.update');
    Route::put('/user/{user}/update-notifications', [UserController::class, 'notificationUpdate'])->name('backoffice.profile.notification.update');

});


