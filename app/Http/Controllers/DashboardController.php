<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\CustomerDetail;

class DashboardController extends Controller
{
    public function mainDashboard() {
        $tenantId = tenant()?->id ?? request()->route('tenant');

        // Sales and orders
        $totalSales = (float) Order::where('tenant_id', $tenantId)->sum('total');
        $totalOrders = (int) Order::where('tenant_id', $tenantId)->count();
        $activeOrders = (int) Order::where('tenant_id', $tenantId)
            ->whereIn('status', ['confirmed','preparing','ready'])
            ->count();
        $avgOrderValue = $totalOrders > 0 ? ($totalSales / $totalOrders) : 0.0;

        // Entities
        $totalUsers = (int) User::where('tenant_id', $tenantId)->count();
        $totalProducts = (int) Product::where('tenant_id', $tenantId)->count();
        $totalCategories = (int) Category::where('tenant_id', $tenantId)->count();

        return view('backoffice.dashboard', [
            'metrics' => [
                'totalSales' => $totalSales,
                'totalOrders' => $totalOrders,
                'activeOrders' => $activeOrders,
                'avgOrderValue' => $avgOrderValue,
                'totalUsers' => $totalUsers,
                'totalProducts' => $totalProducts,
                'totalCategories' => $totalCategories,
            ],
        ]);
    }
}
