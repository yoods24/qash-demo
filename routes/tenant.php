<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;

// Controllers
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TenantNotificationController;
use App\Livewire\Backoffice\TenantNotification;
// Models
use App\Models\Product;

// Livewire
use App\Livewire\Customer\CartPage;
use App\Livewire\Customer\OrderPage;

/*
|--------------------------------------------------------------------------
| Tenant Routes (Path-based)
|--------------------------------------------------------------------------
| All tenant-facing routes (Customer + Backoffice) live here.
| These are mounted under /t/{tenant} for development.
|--------------------------------------------------------------------------
*/

Route::middleware([
    'web',
    InitializeTenancyByPath::class,
    // 'ensure.user.can.access.tenant', // enable after adding the middleware
])->prefix('t/{tenant}')
  ->group(function () {
      // Customer-facing
      Route::get('/', function () {
          return view('customer.home');
      })->name('home');

      Route::get('/menu', function () {
          $products = Product::all();
          return view('customer.menu.index', ['products' => $products]);
      })->name('menu');

      Route::get('/order', OrderPage::class)->name('customer.order');
      Route::get('/cart', CartPage::class)->name('cart.page');

      // Careers (tenant-specific listing)
      Route::get('/career', [CareerController::class, 'indexCustomer'])->name('career.index');

      // Backoffice (tenant-specific)
      Route::prefix('backoffice')->middleware(['auth', 'ensure.user.can.access.tenant'])->group(function() {
          Route::get('/dashboard', function() {
              return view('backoffice.dashboard');
          })->name('backoffice.dashboard');

          // Career management
          Route::get('/career/create', [CareerController::class, 'create'])->name('backoffice.career.create');
          Route::get('/career/{career}/edit', [CareerController::class, 'edit'])->name('backoffice.career.edit');
          Route::post('/career/create/store', [CareerController::class, 'store'])->name('backoffice.career.store')
              ->middleware('permission:career_create');
          Route::delete('/career/{career}/delete', [CareerController::class, 'destroy'])->name('backoffice.career.destroy')
              ->middleware('permission:career_delete');
          Route::get('/career', [CareerController::class, 'indexBackoffice'])->name('backoffice.careers.index');

          // Staffs
          Route::get('/staff', [StaffController::class, 'index'])->name('backoffice.staff.index');
          Route::post('/staff/store', [StaffController::class, 'storeStaff'])->name('backoffice.staff.store');
          Route::get('/roles', [StaffController::class, 'indexRoles'])->name('backoffice.roles.index');
          Route::delete('/staff/{staff}/delete', [StaffController::class, 'destroy'])->name('backoffice.staff.destroy');

          // Sales
          Route::get('/order', [OrderController::class, 'index'])->name('backoffice.order.index');
          Route::get('/order/{order}/view', [OrderController::class, 'view'])->name('backoffice.order.view');

          // Kitchen Board
          Route::get('/kitchen', function() {
              return view('backoffice.kitchen.kitchen-order-index');
          })->name('backoffice.kitchen.index');

          // Role
          Route::post('/roles/create', [StaffController::class, 'storeRole'])->name('backoffice.role.store');
          Route::delete('/roles/{role}/delete', [StaffController::class, 'destroyRole'])->name('backoffice.role.destroy');
          Route::get('/roles/{role}/permissions', [StaffController::class, 'indexPermission'])->name('backoffice.permission.index');
          Route::put('/roles/{role}/permissions/update', [StaffController::class, 'updatePermission'])->name('backoffice.permission.update');

          // Category
          Route::get('/category', [CategoryController::class, 'index'])->name('backoffice.category.index');
          Route::post('/store', [CategoryController::class, 'store'])->name('backoffice.category.store');
          Route::delete('/category/{category}/delete', [CategoryController::class, 'destroy'])->name('backoffice.category.destroy');

          // Product
          Route::get('/product', [ProductController::class, 'index'])
              ->name('backoffice.product.index');
          Route::get('/product/create', [ProductController::class, 'create'])
              ->name('backoffice.product.create');
          Route::post('/product/store', [ProductController::class, 'store'])
              ->name('backoffice.product.store');
          Route::get('/product/{product}/edit', [ProductController::class, 'edit'])
              ->name('backoffice.product.edit');
          Route::delete('/product/{product}/delete', [ProductController::class, 'destroy'])
              ->name('backoffice.product.destroy');
          Route::put('/product/{product}/edit/update', [ProductController::class, 'update'])
              ->name('backoffice.product.update');

          // Product options
          Route::post('/product/{product}/options/store', [ProductController::class, 'optionStore'])->name('backoffice.product.options.store');
          Route::delete('/product/{product}/options/{option}/delete', [ProductController::class, 'optionDestroy'])->name('backoffice.product.options.destroy');
          Route::get('/product/{product}/options/edit', [ProductController::class, 'optionEdit'])->name('backoffice.product.options.edit');
          // Product option values
          Route::post('/product/{product}/options/value/delete', [ProductController::class, 'optionDestroy'])->name('backoffice.product.option.value.destroy');

          // User Manipulate
          Route::get('/user/{user}/edit', [UserController::class, 'edit'])->name('backoffice.user.edit');
          Route::get('/user/{user}/profile-update', [UserController::class, 'profileUpdate'])->name('backoffice.profile.update');
          Route::put('/user/{user}/update-password', [UserController::class, 'passwordUpdate'])->name('backoffice.profile.password.update');
          Route::put('/user/{user}/update-notifications', [UserController::class, 'notificationUpdate'])->name('backoffice.profile.notification.update');

          //  notification page
          Route::get('/notification', [TenantNotificationController::class, 'index'])->name('backoffice.notification.index');
      });
  });
