<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Contracts\View\View;

class MenuGridController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->with(['products' => function ($query) {
                $query->where('active', true)
                    ->orderBy('name');
            }])
            ->whereHas('products', function ($query) {
                $query->where('active', true);
            })
            ->orderBy('name')
            ->get();

        return view('customer.menu.grid', [
            'categories' => $categories,
        ]);
    }
}
