<?php

declare(strict_types=1);

use Livewire\Livewire;
use App\Models\Product;
use App\Models\Category;

// Controllers
use App\Livewire\Customer\CartPage;
use App\Livewire\Customer\OrderPage;
use App\Livewire\BookMenu;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FloorController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FaceRecognitionController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Hrm\ShiftController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CustomerEventController;
// Models
use App\Http\Controllers\DiningTableController;
use App\Http\Controllers\TaxController;
// Livewire
use App\Livewire\Backoffice\TenantNotification;
use App\Http\Controllers\TenantNotificationController;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;

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
      Route::get('/home', function () {
          $menuCategories = Category::with(['products' => function ($query) {
              $query->orderBy('name');
          }])->inRandomOrder()->take(5)->get();

          return view('customer.home', compact('menuCategories'));
      })->name('customer.home');

      Route::get('/menu', function () {
          return view('customer.menu.index');
      })->name('customer.menu');

      Route::get('/menu/book', BookMenu::class)->name('customer.menu.book');


      Route::get('/order', OrderPage::class)->name('customer.order');
      Route::get('/cart', CartPage::class)->name('cart.page');
      Route::get('/payment/{order}', [PaymentController::class, 'show'])->name('payment.show');
      Route::post('/payment/{order}', [PaymentController::class, 'process'])->name('payment.process');
      Route::get('/payment/success/{order}', [PaymentController::class, 'success'])->name('payment.success');
      Route::get('/payment/failed/{order}', [PaymentController::class, 'failed'])->name('payment.failed');
      Route::get('/order/track/{order}', [OrderTrackingController::class, 'show'])->name('order.track');

      // Careers (tenant-specific listing & detail)
      Route::get('/career', [CareerController::class, 'indexCustomer'])->name('customer.career.index');
      Route::get('/career/{career}', [CareerController::class, 'showCustomer'])->name('customer.career.show');
      Route::get('/events', [CustomerEventController::class, 'index'])->name('customer.events.index');
      Route::get('/events/{event}', [CustomerEventController::class, 'show'])->name('customer.events.show');

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
          Route::view('/customers', 'backoffice.customers.index')->name('backoffice.customers.index');

          Route::get('/taxes', [TaxController::class, 'index'])->name('backoffice.taxes.index');
          Route::post('taxes/store', [TaxController::class, 'store'])->name('backoffice.taxes.store');
          // Staffs
          Route::get('/staff', [StaffController::class, 'index'])->name('backoffice.staff.index')
              ->middleware('permission:hrm_employees_view');
          Route::get('/staff/{staff}/view', [StaffController::class, 'view'])->name('backoffice.staff.view');
          Route::get('staff/create', [StaffController::class, 'create'])->name('backoffice.staff.create');
          Route::post('/staff/store', [StaffController::class, 'storeStaff'])->name('backoffice.staff.store');
          // New full create form with collapsible sections
          Route::get('/user/create', [StaffController::class, 'createFull'])->name('backoffice.user.create');
          Route::post('/user/store', [StaffController::class, 'storeFull'])->name('backoffice.user.store');
          Route::get('/roles', [StaffController::class, 'indexRoles'])->name('backoffice.roles.index')
              ->middleware('permission:hrm_roles_view');
          Route::delete('/staff/{staff}/delete', [StaffController::class, 'destroy'])->name('backoffice.staff.destroy');
          

          //  Shifts
          Route::get('/shift', [ShiftController::class, 'index'])->name('backoffice.shift.index')
              ->middleware('permission:hrm_shifts_view');

          // Sales
          Route::get('/order', [OrderController::class, 'index'])->name('backoffice.order.index')
              ->middleware('permission:sales_view');
          Route::get('/order/{order}/view', [OrderController::class, 'view'])->name('backoffice.order.view');

          // Kitchen Board
          Route::get('/kitchen', function() {
              return view('backoffice.kitchen.kitchen-order-index');
          })->name('backoffice.kitchen.index')
            ->middleware('permission:kitchen_view');

          // POS page (sidebar hidden)
          Route::get('/pos', function () {
              return view('backoffice.pos.index');
          })->name('backoffice.pos.index')
            ->middleware('permission:sales_view');

          // Reports
          Route::get('/reports', [\App\Http\Controllers\ReportsController::class, 'index'])->name('backoffice.reports.index');
          Route::get('/reports/sales', [\App\Http\Controllers\ReportsController::class, 'sales'])->name('backoffice.reports.sales');
          Route::get('/reports/products', [\App\Http\Controllers\ReportsController::class, 'productsPurchase'])->name('backoffice.reports.products');
          Route::get('/reports/kitchen', [\App\Http\Controllers\ReportsController::class, 'kitchenPerformance'])->name('backoffice.reports.kitchen');

          // Dining Tables
          Route::get('/diningTable', [DiningTableController::class, 'index'])->name('backoffice.tables.index')
              ->middleware('permission:pos_table_orders_view');
          // Waiter/Waitress plan view (Livewire component)
          Route::get('/tables/plan', function () {
              return view('backoffice.tables.plan');
          })->name('backoffice.tables.plan')->middleware('permission:pos_table_orders_view');
          // Table Information (Filament table list with filters)
          Route::get('/tables/info',[DiningTableController::class, 'information'])->name('backoffice.tables.info')
              ->middleware('permission:pos_table_orders_view');
          // QR for a specific table
          Route::get('/tables/{dining_table}/qr', [DiningTableController::class, 'qr'])->name('backoffice.tables.qr')
              ->middleware('permission:pos_table_orders_view');
          Route::post('/tables/{dining_table}/qr/generate', [DiningTableController::class, 'generateQr'])->name('backoffice.tables.qr.generate')
              ->middleware('permission:pos_table_orders_view');
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
          Route::get('/category', [CategoryController::class, 'index'])->name('backoffice.category.index')
              ->middleware('permission:inventory_category_view');
          Route::post('/store', [CategoryController::class, 'store'])->name('backoffice.category.store');
          Route::delete('/category/{category}/delete', [CategoryController::class, 'destroy'])->name('backoffice.category.destroy');

          // Product
          Route::get('/product', [ProductController::class, 'index'])
              ->name('backoffice.product.index')
              ->middleware('permission:inventory_products_view');
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

          // Events
          Route::get('/events', [EventController::class, 'index'])
              ->name('backoffice.events.index')
              ->middleware('permission:create_event|delete_event');
          Route::get('/events/create', [EventController::class, 'create'])
              ->name('backoffice.events.create')
              ->middleware('permission:create_event');
          Route::post('/events', [EventController::class, 'store'])
              ->name('backoffice.events.store')
              ->middleware('permission:create_event');
          Route::get('/events/{event}/edit', [EventController::class, 'edit'])
              ->name('backoffice.events.edit')
              ->middleware('permission:create_event');
          Route::put('/events/{event}', [EventController::class, 'update'])
              ->name('backoffice.events.update')
              ->middleware('permission:create_event');
          Route::delete('/events/{event}', [EventController::class, 'destroy'])
              ->name('backoffice.events.destroy')
              ->middleware('permission:delete_event');

          // User Manipulate
          Route::get('/user/{user}/edit', [UserController::class, 'edit'])->name('backoffice.user.edit');
          Route::get('/user/{user}/profile-update', [UserController::class, 'profileUpdate'])->name('backoffice.profile.update');
          Route::put('/user/{user}/update-password', [UserController::class, 'passwordUpdate'])->name('backoffice.profile.password.update');
          Route::put('/user/{user}/update-notifications', [UserController::class, 'notificationUpdate'])->name('backoffice.profile.notification.update');

          // HRM - Shifts
          Route::get('/hrm/shifts', [ShiftController::class, 'index'])->name('backoffice.shift.index')
              ->middleware('permission:hrm_shifts_view');
          Route::post('/hrm/shifts', [ShiftController::class, 'store'])->name('backoffice.shift.store');

          // HRM - Attendance (self service)
          Route::get('/hrm/attendance', function () {
              return view('backoffice.hrm.attendance.index');
          })->name('backoffice.attendance.index');


          Route::controller(SettingsController::class)
            ->prefix('/settings')
                ->group(function () {
            Route::get('/', 'index')
                ->name('backoffice.settings.index');
            Route::get('/attendance', 'attendanceShow')
                ->name('backoffice.settings.attendance-settings');
            Route::get('/geolocation', 'geolocationShow')
                    ->name('backoffice.settings.geolocation-settings');
          });

          // Facial Recognition routes 
          Route::get('/hrm/face-attendance', [FaceRecognitionController::class, 'attendance'])
            ->name('backoffice.face.attendance');   
          Route::get('/hrm/face-register', [FaceRecognitionController::class, 'register'])
            ->name('backoffice.face.register');   
          Route::post('/hrm/face-attendance/confirm', [FaceRecognitionController::class, 'confirm'])
            ->name('backoffice.face.confirm');

          //  notification page
          Route::get('/notification', [TenantNotificationController::class, 'index'])->name('backoffice.notification.index');
      });
  });
