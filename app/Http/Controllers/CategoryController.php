<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index() {
        $category = Category::paginate(10);
        return view('backoffice.product.category', ['categories' => $category]);
    }

public function store(Request $request)
{
    $validated = $request->validate([
        'image_url' => ['nullable', File::types(['png', 'jpg', 'webp'])],
        'name' => ['required', 'max:100']
    ]);

    $imagePath = null;

    if ($request->hasFile('image_url')) {
        $imagePath = $request->file('image_url')->store('category', 'public');
    }

    Category::create([
        'name' => $validated['name'],
        'image_url' => $imagePath
    ]);

    return redirect()->back()->with('message', 'Category created!');
}


public function update(Request $request, Category $category)
{
    $validated = $request->validate([
        'image_url' => ['nullable', File::types(['png', 'jpg', 'webp'])],
        'name' => ['required', 'max:100']
    ]);

    if ($request->hasFile('image_url')) {
        // Delete old image if exists
        if ($category->image_url && Storage::disk('public')->exists($category->image_url)) {
            Storage::disk('public')->delete($category->image_url);
        }

        // Store new image
        $imagePath = $request->file('image_url')->store('category', 'public');
        $category->image_url = $imagePath;
    }

    $category->name = $validated['name'];
    $category->save();

    return redirect()->back()->with('message', 'Category updated!');
}



    public function destroy(Category $category)
    {
        // Delete image file from storage
        if ($category->image_url && Storage::disk('public')->exists($category->image_url)) {
            Storage::disk('public')->delete($category->image_url);
        }

        $category->delete();

        return redirect()->back()->with('message', 'Category deleted!');
    }
}
