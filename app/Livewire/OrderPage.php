<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductOptionValue;
use Darryldecode\Cart\Facades\CartFacade as Cart;
use Illuminate\Support\Facades\Session;
use App\Models\CustomerDetail;

class OrderPage extends Component
{
    public $categories;
    public $products;
    public $selectedCategory = 'all';
    public $selectedProduct = null;
    public $selectedOptions = [];
    public $quantity = 1;
    public $showOptionModal = false;
    public $showCustomerModal = false;
    public $customerName = '';
    public $customerEmail = '';
    public $customerGender = null; // 'man' | 'women' | null
    public $username = null;
    public $pendingAction = null; // 'add_to_cart' | 'update_profile' | null
    public $tenantId = null;   // current tenant id
    public $tenantName = null; // display-only name

    public function mount()
    {
        // Resolve tenant context for display & fallbacks
        $tenant = tenant();
        $this->tenantId = $tenant?->id ?? request()->route('tenant');
        $this->tenantName = ($tenant?->data['name'] ?? null) ?: ($tenant?->id ?? '');

        $this->categories = Category::all();
        $this->loadProducts();

        // Preload customer from session if exists (for greeting/UI)
        if (Session::has('customer_detail_id')) {
            $customer = CustomerDetail::find(Session::get('customer_detail_id'));
            if ($customer) {
                $this->username       = $customer->name;
                $this->customerName   = $customer->name;   // prefill modal if reopened
                $this->customerEmail  = $customer->email;
                $this->customerGender = $customer->gender;
            }
        }
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
        // Require customer details before adding to cart
        if (!Session::has('customer_detail_id')) {
            $this->pendingAction = 'add_to_cart';
            $this->showCustomerModal = true;
            return;
        }

        $this->performAddToCart();
    }

    private function performAddToCart()
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

    public function cancelCustomerModal()
    {
        $this->showCustomerModal = false;
    }

    public function saveCustomer()
    {
        $data = $this->validate([
            'customerName'   => 'required|string|max:255',
            'customerEmail'  => 'required|email|max:255',
            'customerGender' => 'nullable|in:man,women,none',
        ]);

        $gender = ($data['customerGender'] ?? null) === 'none' ? null : ($data['customerGender'] ?? null);

        // Resolve tenant id robustly for Livewire requests (fallback to bound property)
        $tenantId = tenant()?->id ?? request()->route('tenant') ?? $this->tenantId;
        $customer = CustomerDetail::firstOrCreate(
            ['email' => $data['customerEmail'], 'tenant_id' => $tenantId],
            ['name' => $data['customerName'], 'gender' => $gender, 'tenant_id' => $tenantId]
        );

        // Update name/gender if changed
        $updates = [];
        if ($customer->name !== $data['customerName']) {
            $updates['name'] = $data['customerName'];
        }
        if (($customer->gender ?? null) !== $gender) {
            $updates['gender'] = $gender;
        }
        if (!empty($updates)) {
            $customer->update($updates);
        }

        Session::put('customer_detail_id', $customer->id);
        $this->username = $customer->name; // reflect on UI header
        $this->customerEmail = $customer->email;

        $this->showCustomerModal = false;
        // Decide next action after saving details
        if ($this->pendingAction === 'add_to_cart') {
            $this->performAddToCart();
        } else {
            session()->flash('success', 'Details updated.');
        }
        $this->pendingAction = null;
    }

    public function openCustomerEdit()
    {
        $customer = Session::has('customer_detail_id')
            ? CustomerDetail::find(Session::get('customer_detail_id'))
            : null;

        if ($customer) {
            $this->customerName   = $customer->name;
            $this->customerEmail  = $customer->email;
            $this->customerGender = $customer->gender; // may be null
        }

        $this->pendingAction = 'update_profile';
        $this->showCustomerModal = true;
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

        // Use already-loaded relations to avoid extra DB queries on every render
        foreach ($this->selectedProduct->options as $option) {
            $valueId = $this->selectedOptions[$option->id] ?? null;
            if ($valueId) {
                $value = $option->values->firstWhere('id', $valueId);
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
