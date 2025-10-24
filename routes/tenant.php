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
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TenantNotificationController;
use App\Http\Controllers\DiningTableController;
use App\Http\Controllers\FloorController;
use App\Http\Controllers\Hrm\ShiftController;
use App\Livewire\Backoffice\TenantNotification;
use Livewire\Livewire;
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
          
        Route::get('/dashboard', [DashboardController::class, 'mainDashboard'])
        ->name('backoffice.dashboard');

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
          Route::get('/staff/{staff}/view', [StaffController::class, 'view'])->name('backoffice.staff.view');
          Route::get('staff/create', [StaffController::class, 'create'])->name('backoffice.staff.create');
          Route::post('/staff/store', [StaffController::class, 'storeStaff'])->name('backoffice.staff.store');
          // New full create form with collapsible sections
          Route::get('/user/create', [StaffController::class, 'createFull'])->name('backoffice.user.create');
          Route::post('/user/store', [StaffController::class, 'storeFull'])->name('backoffice.user.store');
          Route::get('/roles', [StaffController::class, 'indexRoles'])->name('backoffice.roles.index');
          Route::delete('/staff/{staff}/delete', [StaffController::class, 'destroy'])->name('backoffice.staff.destroy');
          

          //   Shifts
          Route::get('/shift', [ShiftController::class, 'index'])->name('backoffice.shift.index');

          // Sales
          Route::get('/order', [OrderController::class, 'index'])->name('backoffice.order.index');
          Route::get('/order/{order}/view', [OrderController::class, 'view'])->name('backoffice.order.view');

          // Kitchen Board
          Route::get('/kitchen', function() {
              return view('backoffice.kitchen.kitchen-order-index');
          })->name('backoffice.kitchen.index');

          // Dining Tables
          Route::get('/tables', [DiningTableController::class, 'index'])->name('backoffice.tables.index');
          Route::post('/tables', [DiningTableController::class, 'store'])->name('backoffice.tables.store');
          Route::put('/tables/positions', [DiningTableController::class, 'updatePositions'])->name('backoffice.tables.positions');
          Route::put('/tables/{dining_table}', [DiningTableController::class, 'update'])->name('backoffice.tables.update');
          Route::delete('/tables/{dining_table}', [DiningTableController::class, 'destroy'])->name('backoffice.tables.destroy');

          // Floors
          Route::post('/floors', [FloorController::class, 'store'])->name('backoffice.floors.store');
          Route::put('/floors/{floor}', [FloorController::class, 'update'])->name('backoffice.floors.update');
          Route::delete('/floors/{floor}', [FloorController::class, 'destroy'])->name('backoffice.floors.destroy');

          // Role
          Route::post('/roles/create', [StaffController::class, 'storeRole'])->name('backoffice.role.store');
            // without redirect
          Route::post('/roles/create/wr', [StaffController::class, 'storeRoleWr'])->name('backoffice.role.store-wr');
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

          // HRM - Shifts
          Route::get('/hrm/shifts', [ShiftController::class, 'index'])->name('backoffice.shift.index');
          Route::post('/hrm/shifts', [ShiftController::class, 'store'])->name('backoffice.shift.store');

          // HRM - Attendance (self service)
          Route::get('/hrm/attendance', function () {
              return view('backoffice.hrm.attendance.index');
          })->name('backoffice.attendance.index');

          // Settings (non-Filament)
          Route::get('/settings', function () {
              return view('backoffice.settings.index');
          })->name('backoffice.settings.index');

          // Face verification placeholder
          Route::get('/hrm/attendance/face', function () {
              return view('backoffice.hrm.attendance.face-verify');
          })->name('backoffice.attendance.face');

          //  notification page
          Route::get('/notification', [TenantNotificationController::class, 'index'])->name('backoffice.notification.index');
      });
  });
