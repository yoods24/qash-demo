<?php

namespace App\Http\Controllers;

use App\Http\Requests\backoffice\ProductCreateRequest;
use App\Http\Requests\backoffice\ProductOptionStoreRequest;
use App\Http\Requests\backoffice\ProductUpdateRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductOption;
use App\Services\Product\ProductNotificationService;
use App\Services\Product\ProductService;

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
    public function store(
        ProductCreateRequest $request,
        ProductService $service,
        ProductNotificationService $notification
    ) {
        $product = $service->create($request->validated(), $request->file('product_image'));
        $notification->created($product, $request->user());

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

    public function update(
        ProductUpdateRequest $request,
        Product $product,
        ProductService $service
    ) {
        $service->update($product, $request->validated(), $request->file('product_image'));

        return redirect()
            ->route('backoffice.product.index')
            ->with('message', 'Product updated successfully.');
    }

    public function destroy(Product $product, ProductService $service) {
        $service->delete($product);

        return redirect()->route('backoffice.product.index')->with('message', 'Product deleted successfully.');
    }
    // OPTIONS
    public function optionStore(
        ProductOptionStoreRequest $request,
        Product $product,
        ProductService $service
    ) {
        $data = $request->validated();
        $service->addOption($product, $data);

        return redirect()
            ->route('backoffice.product.edit', $product->id)
            ->with('message', "Option '{$data['option_name']}' for product '{$product->name}' added successfully.");
    }

    public function optionDestroy(
        Product $product,
        ProductOption $option,
        ProductService $service
    ) {
        $service->deleteOption($product, $option);

        return redirect()
            ->back()
            ->with('message', "Option '{$option->name}' deleted successfully.");
    }
}
