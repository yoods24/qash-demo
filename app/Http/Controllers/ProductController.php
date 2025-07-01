<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index() {
        $products = Product::with('category')->paginate(10); 
        return view('backoffice.product.index', ['products' => $products]);
    }


    public function create()
{
    $categories = Category::all();

    return view('backoffice.product.create', compact('categories'));
}
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'alternate_name' => 'nullable|string|max:255',
            'price' => 'required|numeric',
            'goods_price' => 'nullable|numeric',
            'product_image' => 'nullable|image|mimes:jpg,jpeg,png',
            'featured' => 'nullable|boolean',
        ]);

        // Handle File Upload
        if ($request->hasFile('product_image')) {
            
        }

        // Ensure featured is properly set
        $validated['featured'] = $request->has('featured') ? 1 : 0;

        // Store Product
        Product::create($validated);

        return redirect()->route('backoffice.product.index')->with('message', 'Product created successfully.');
    }


    public function edit(Product $product) {
        $categories = Category::where('id', '!=', $product->category_id)->get();

        return view('backoffice.product.edit', ['product' => $product, 'categories' => $categories]);
    }
}
