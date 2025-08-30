<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductOptionValue;
use Darryldecode\Cart\Facades\CartFacade as Cart;

class OrderPage extends Component
{
    public $categories;
    public $products;
    public $selectedCategory = 'all';
    public $selectedProduct = null;
    public $selectedOptions = [];
    public $quantity = 1;
    public $showOptionModal = false;

    public function mount()
    {
        $this->categories = Category::all();
        $this->loadProducts();
    }

    private function loadProducts()
    {
        $this->products = Product::with('category')
            ->get()
            ->groupBy('category_id')
            ->map(function ($items, $categoryId) {
                return [
                    'id' => $categoryId,
                    'name' => $items->first()->category->name ?? 'Uncategorized',
                    'items' => $items->take(4),
                ];
            });
    }

    public function filterCategory($categoryId)
    {
        $this->selectedCategory = $categoryId;

        if ($categoryId === 'all') {
            $this->loadProducts();
        } else {
            $category = Category::find($categoryId);
            $this->products = collect([
                $categoryId => [
                    'id' => $categoryId,
                    'name' => $category->name,
                    'items' => Product::where('category_id', $categoryId)->get(),
                ]
            ]);
        }

        $this->dispatch('categoryUpdated');
    }

    public function showProductOptions($productId)
    {
        $this->selectedProduct = Product::with(['options.values'])->findOrFail($productId);

        // reset options
        $this->selectedOptions = [];
        foreach ($this->selectedProduct->options as $option) {
            $this->selectedOptions[$option->id] = null;
        }

        $this->quantity = 1;
        $this->showOptionModal = true;

        $this->dispatch('lock-scroll');
    }
    public function incrementQuantity()
    {
        $this->quantity++;
    }

    public function decrementQuantity()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function addSelectedProductToCart()
    {
        if (!$this->selectedProduct) {
            return;
        }

        $price = $this->selectedProduct->price;
        $options = [];

        foreach ($this->selectedOptions as $optionId => $valueId) {
            if ($valueId) {
                $value = ProductOptionValue::find($valueId);
                if ($value) {
                    $options[$optionId] = [
                        'id' => $value->id,
                        'value' => $value->value,
                        'price_adjustment' => $value->price_adjustment,
                    ];
                    $price += $value->price_adjustment;
                }
            }
        }

        Cart::add([
            'id' => $this->selectedProduct->id,
            'name' => $this->selectedProduct->name,
            'price' => $price,
            'quantity' => $this->quantity,
            'attributes' => [
                'options'     => $options,
                'base_price'  => $this->selectedProduct->price,
                'image'       => $this->selectedProduct->product_image ?? null,
                'description' => $this->selectedProduct->description ?? null,
                'category'    => $this->selectedProduct->category->name ?? null,
            ],
        ]);

        $this->reset(['selectedProduct', 'selectedOptions', 'quantity', 'showOptionModal']);
        $this->dispatch('unlock-scroll');
        $this->dispatch('cart-updated');
    }

    public function closeOptionModal()
    {
        $this->showOptionModal = false;
        $this->dispatch('unlock-scroll');
    }

    // 👇 Computed property that always reacts
    public function getTotalPriceProperty()
    {
        if (!$this->selectedProduct) {
            return 0;
        }

        $total = $this->selectedProduct->price;

        foreach ($this->selectedOptions as $optionId => $valueId) {
            if ($valueId) {
                $value = ProductOptionValue::find($valueId);
                if ($value) {
                    $total += $value->price_adjustment;
                }
            }
        }

        return $total * $this->quantity;
    }

    public function render()
    {
        return view('livewire.order-page', [
            'products'         => $this->products,
            'featuredProducts' => Product::where('featured', 1)->get(),
            'cartTotal'        => Cart::getTotal(),
            'cartQuantity'     => Cart::getTotalQuantity()
        ]);
    }
}
