<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\CustomerDetail;

class DashboardController extends Controller
{
    public function mainDashboard() {
        $tenantId = tenant()?->id ?? request()->route('tenant');
        $user = request()->user();

        // Default staff dashboard for non-admin users
        if ($user && ! $user->is_admin) {
            $metrics = $this->employeeMetrics($user, $tenantId);

            return view('backoffice.employee-dashboard', $metrics);
        }

        // Sales and orders
        $totalSales = (float) Order::where('tenant_id', $tenantId)->sum('total');
        $totalOrders = (int) Order::where('tenant_id', $tenantId)->count();
        $activeOrders = (int) Order::where('tenant_id', $tenantId)
            ->whereIn('status', ['confirmed','preparing'])
            ->count();
        $avgOrderValue = $totalOrders > 0 ? ($totalSales / $totalOrders) : 0.0;

        // Entities
        $totalUsers = (int) User::where('tenant_id', $tenantId)->count();
        $totalProducts = (int) Product::where('tenant_id', $tenantId)->count();
        $outOfStockProducts = (int) Product::where('tenant_id', $tenantId)
            ->where('stock_qty', '<=', 0)
            ->count();
        $totalCategories = (int) Category::where('tenant_id', $tenantId)->count();

        // Attendance today (clocked-in users vs total employees)
        $today = now()->toDateString();
        $presentToday = (int) Attendance::where('tenant_id', $tenantId)
            ->whereDate('work_date', $today)
            ->whereNotNull('clock_in_at')
            ->distinct('user_id')
            ->count('user_id');
        $totalEmployees = (int) User::where('tenant_id', $tenantId)->count();

        return view('backoffice.dashboard', [
            'metrics' => [
                'totalSales' => $totalSales,
                'totalOrders' => $totalOrders,
                'activeOrders' => $activeOrders,
                'avgOrderValue' => $avgOrderValue,
                'totalUsers' => $totalUsers,
                'totalProducts' => $totalProducts,
                'outOfStockProducts' => $outOfStockProducts,
                'totalCategories' => $totalCategories,
                'presentToday' => $presentToday,
                'totalEmployees' => $totalEmployees,
            ],
        ]);
    }

    /**
     * Build a lightweight dashboard payload for frontline staff.
     */
    private function employeeMetrics(User $user, string|int|null $tenantId): array
    {
        $today = now()->toDateString();

        $attendance = Attendance::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->latest('clock_in_at')
            ->first();

        $ordersQuery = Order::query()
            ->whereIn('status', ['served', 'completed'])
            ->whereDate('updated_at', $today)
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId));

        $ordersServed = (clone $ordersQuery)->count();
        $salesHandled = (float) (clone $ordersQuery)->sum(DB::raw('COALESCE(grand_total, total, 0)'));

        $clockInTime = $attendance?->clock_in_at?->format('H:i');
        $roleName = $user->roles()->first()?->name ?? 'Employee';

        return [
            'greeting' => $this->greetingMessage($user),
            'roleName' => $roleName,
            'attendance' => [
                'isPresent' => $attendance?->clock_in_at !== null,
                'clockInTime' => $clockInTime,
            ],
            'ordersServed' => $ordersServed,
            'salesHandled' => $salesHandled,
            'user' => $user,
        ];
    }

    private function greetingMessage(User $user): string
    {
        $hour = now()->hour;
        $prefix = match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };

        return $prefix . ', ' . ($user->fullName() ?: $user->first_name ?? 'there');
    }
}
