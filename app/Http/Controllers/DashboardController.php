<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\CustomerDetail;

class DashboardController extends Controller
{
    public function mainDashboard() {
        $products = Product::count();
        $customers = CustomerDetail::count();
        
        return view('backoffice.dashboard');
    }
}
