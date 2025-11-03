<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use App\Models\Product;
use App\Models\DiningTable;
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
    public $showTableModal = false;
    public $customerName = '';
    public $customerEmail = '';
    public $customerGender = null; // 'man' | 'women' | null
    public $username = null;
    public $pendingAction = null; // 'add_to_cart' | 'update_profile' | null
    public $tenantId = null;   // current tenant id
    public $tenantName = null; // display-only name
    public $currentTable = null; // label for current dining table

    public function mount()
    {
        // Resolve tenant context for display & fallbacks
        $tenant = tenant();
        $this->tenantId = $tenant?->id ?? request()->route('tenant');
        $this->tenantName = ($tenant?->data['name'] ?? null) ?: ($tenant?->id ?? '');

        $this->categories = Category::all();
        $this->loadProducts();

        // If a QR code or table id is specified (from QR), assign the table into the session
        $code = request()->query('code');
        if ($code) {
            $tenantId = $this->tenantId;
            $t = DiningTable::where('tenant_id', $tenantId)->where('qr_code', $code)->first();
            if ($t) {
                Session::put('dining_table_id', (int) $t->id);
            }
        } else {
            $table = request()->query('table');
            if ($table && is_numeric($table)) {
                Session::put('dining_table_id', (int) $table);
            }
        }

        // Preload customer from session if exists (for greeting/UI)
        if (Session::has('customer_detail_id')) {
            $customer = CustomerDetail::find(Session::get('customer_detail_id'));
            if ($customer) {
                $this->username       = $customer->name;
                $this->customerName   = $customer->name;   // prefill modal if reopened
                $this->customerEmail  = $customer->email;
                $this->customerGender = $customer->gender;
                // Persist current table to the customer's details (pre-payment)
                $tableId = Session::get('dining_table_id');
                if ($tableId && $customer->dining_table_id !== (int) $tableId) {
                    $customer->update(['dining_table_id' => (int) $tableId]);
                }
            }
        }

        // Expose current table for UI
        $this->syncCurrentTable();
    }

    private function syncCurrentTable(): void
    {
        $tableId = Session::get('dining_table_id');
        if ($tableId) {
            $t = DiningTable::where('tenant_id', $this->tenantId)->find($tableId);
            $this->currentTable = $t?->label;
        } else {
            $this->currentTable = null;
        }
    }

    private function loadProducts()
    {
        $this->products = Product::with('category')
            ->where('active', 1)
            ->where('stock_qty', '>', 0)
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
                    'items' => Product::where('category_id', $categoryId)
                        ->where('active', 1)
                        ->where('stock_qty', '>', 0)
                        ->get(),
                ]
            ]);
        }
        $this->dispatch('categoryUpdated');
    }

    public function showProductOptions($productId)
    {
        $this->selectedProduct = Product::with(['options.values'])->findOrFail($productId);

        // guard: hide/deny if inactive or out of stock
        if (($this->selectedProduct->active ?? 1) != 1 || (int)($this->selectedProduct->stock_qty ?? 0) <= 0) {
            session()->flash('error', 'This item is unavailable.');
            return;
        }

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
        // Re-check availability
        $fresh = Product::find($this->selectedProduct->id);
        if (!$fresh || ($fresh->active ?? 1) != 1 || (int)($fresh->stock_qty ?? 0) <= 0) {
            session()->flash('error', 'This item is unavailable.');
            $this->reset(['selectedProduct', 'selectedOptions', 'quantity', 'showOptionModal']);
            $this->dispatch('unlock-scroll');
            return;
        }
        // Require dining table assignment before adding to cart
        if (!Session::has('dining_table_id') || !Session::get('dining_table_id')) {
            $this->showTableModal = true;
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

    public function closeTableModal()
    {
        $this->showTableModal = false;
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
                'estimated_seconds' => (int) ($this->selectedProduct->estimated_seconds ?? 0),
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
        $defaults = ['name' => $data['customerName'], 'gender' => $gender, 'tenant_id' => $tenantId];
        $tableId = Session::get('dining_table_id');
        if ($tableId) {
            $defaults['dining_table_id'] = (int) $tableId;
        }
        $customer = CustomerDetail::firstOrCreate(
            ['email' => $data['customerEmail'], 'tenant_id' => $tenantId],
            $defaults
        );

        // Update name/gender if changed
        $updates = [];
        if ($customer->name !== $data['customerName']) {
            $updates['name'] = $data['customerName'];
        }
        if (($customer->gender ?? null) !== $gender) {
            $updates['gender'] = $gender;
        }
        if ($tableId && $customer->dining_table_id !== (int) $tableId) {
            $updates['dining_table_id'] = (int) $tableId;
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
        $this->syncCurrentTable();
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
        return view('livewire.customer.order-page', [
            'products'         => $this->products,
            'featuredProducts' => Product::where('featured', 1)
                ->where('active', 1)
                ->where('stock_qty', '>', 0)
                ->get(),
            'cartTotal'        => Cart::getTotal(),
            'cartQuantity'     => Cart::getTotalQuantity()
        ]);
    }
}
