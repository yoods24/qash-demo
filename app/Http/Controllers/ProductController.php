<?php

namespace App\Http\Controllers;

use Log;
use App\Models\Product;
use App\Models\Category;
use Darryldecode\Cart\Cart;
use Illuminate\Http\Request;
use App\Models\ProductOption;
use App\Models\TenantNotification;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Facades\Tenancy;
use Illuminate\Support\Facades\Storage;

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

        // Get tenant model
        $tenant = tenant(); // or Tenancy::getTenant()

        // Tenant ID (the primary key of your tenants table)
        $tenantId = $tenant->id;
        $u = $request->user();
        $userName = trim(($u->firstName ?? '') . ' ' . ($u->lastName ?? '')) ?: ($u->email ?? 'user');

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'alternate_name' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'goods_price' => 'nullable|numeric|min:0',
            'product_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'featured' => 'nullable|boolean',
            'description' => 'nullable|max:255|string'
        ]);

         $imagePath = null;
        // Handle File Upload
        if ($request->hasFile('product_image')) {
            $imagePath = $request->file('product_image')->store('products', 'public');
        }

        // Ensure featured is properly set
        $validated['featured'] = $request->has('featured') ? 1 : 0;
        // Store Product
        $product = Product::create([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'alternate_name' => $validated['alternate_name'] ?? null,
            'price'=> $validated['price'],
            'goods_price'=> $validated['goods_price'] ?? null,
            'product_image' => $imagePath,
            'description' => $validated['description'] ?? null,
            'featured' => $validated['featured'],
        ]);
        try {
            TenantNotification::create([
                'tenant_id'    => $tenantId,
                'type'         => 'product',
                'title'        => 'New Product Created',
                'description'  => "Product '{$validated['name']}' has been created by {$userName}.",
                'item_id'      => $product->id,
                'route_name'   => 'backoffice.product.edit',
                // Pass array; let the model cast it to JSON. Use proper route param name (e.g. 'product')
                'route_params' => ['product' => $product->id, 'tenant' => $tenantId],
            ]);
        } catch (\Throwable $e) {
            ds($e);
        }


        return redirect()->route('backoffice.product.index')->with('message', 'Product created successfully.');
    }


    public function edit(Product $product) {
        $categories = Category::where('id', '!=', $product->category_id)->get();
        $product->load('options.values'); // No need to re-find, just eager load

        return view('backoffice.product.edit', [
            'product' => $product,
            'categories' => $categories
        ]);
    }

    public function update(Request $request, Product $product)
    {
        // Validate incoming request
        $validated = $request->validate([
            'category_id'   => 'required|exists:categories,id',
            'name'          => 'required|string|max:255',
            'alternate_name'=> 'nullable|string|max:255',
            'price'         => 'required|numeric|min:0',
            'goods_price'   => 'nullable|numeric|min:0',
            'product_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'featured'      => 'nullable|boolean',
            'description'   => 'nullable|string|max:255',
        ]);

        // Handle image update
        $imagePath = $product->product_image; // keep old image by default
        if ($request->hasFile('product_image')) {
            // delete old image if exists
            if ($product->product_image && Storage::disk('public')->exists($product->product_image)) {
                Storage::disk('public')->delete($product->product_image);
            }
            // store new image
            $imagePath = $request->file('product_image')->store('products', 'public');
        }

        // Set featured (checkbox handling)
        $validated['featured'] = $request->has('featured') ? 1 : 0;

        // Update product
        $product->update([
            'category_id'   => $validated['category_id'],
            'name'          => $validated['name'],
            'alternate_name'=> $validated['alternate_name'] ?? null,
            'price'         => $validated['price'],
            'goods_price'   => $validated['goods_price'] ?? null,
            'product_image' => $imagePath,
            'description'   => $validated['description'] ?? null,
            'featured'      => $validated['featured'],
        ]);

        return redirect()
            ->route('backoffice.product.index')
            ->with('message', 'Product updated successfully.');
    }

    public function destroy(Product $product) {
        if ($product->product_image && Storage::disk('public')->exists($product->product_image)) {
        Storage::disk('public')->delete($product->product_image);
        }
        $product->delete();
        return redirect()->route('backoffice.product.index')->with('message', 'Product deleted successfully.');
    }
    // OPTIONS
    public function optionStore(Request $request, Product $product)
    {
        $productName = $product->name;
        // Validate the request
        $validated = $request->validate([
            'option_name' => 'required|string|max:255',
            'values' => 'required|array|min:1',
            'values.*.value' => 'required|string|max:255',
            'values.*.price_change' => 'nullable|numeric',
        ]);

        DB::transaction(function () use ($product, $validated) {
            // Create the product option
            $productOption = ProductOption::create([
                'product_id' => $product->id,
                'name' => $validated['option_name'],
            ]);

            // Loop through each option value
            foreach ($validated['values'] as $valueData) {
                \App\Models\ProductOptionValue::create([
                    'product_option_id' => $productOption->id,
                    'value' => $valueData['value'],
                    'price_adjustment' => $valueData['price_change'] ?? 0,
                ]);
            }
        });

        return redirect()
            ->route('backoffice.product.edit', $product->id)
            ->with('message', "Option '{$validated['option_name']}' for product '{$productName}' added successfully.");
    }

    public function optionDestroy(Product $product, ProductOption $option)
    {
        if ($option->product_id !== $product->id) {
            abort(403, 'Unauthorized');
        }

        $option->values()->delete();
        $option->delete();


        return redirect()
            ->back()
            ->with('message', "Option '{$option->name}' deleted successfully.");
        }
    }
