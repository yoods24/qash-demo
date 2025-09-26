<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductOptionValue;
use Livewire\Component;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Darryldecode\Cart\Facades\CartFacade as Cart;

class CartPage extends Component
{
    public $tenantId = null;
    public $items = [];
    public $total = 0;
    public $grandTotal = 0;
    public $removeItemModal = false;
    public $removingItemId = null;
    public $removingItemName = '';
    public $selectedProduct = null;
    public $selectedOptions = [];
    public $showOptionModal = false;
    public $quantity = 1;
    public $editingItemId = null; // if set, modal updates existing cart item
    
    public $softwareService = 1000;


    public function mount()
    {
        $this->tenantId = tenant()?->id ?? request()->route('tenant');
        $this->refreshCart();
    }

    public function refreshCart()
    {
        $this->items = Cart::getContent();
        $this->total = Cart::getTotal();
        // Always include software/service fee when there is at least one item
        $hasItems = $this->items && count($this->items) > 0;
        $this->grandTotal = $this->total + ($hasItems ? ($this->softwareService ?? 0) : 0);
    }

        public function closeOptionModal()
    {
        $this->showOptionModal = false;
        $this->dispatch('unlock-scroll');
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
        $this->editingItemId = null; // creating a new item, not editing
        $this->showOptionModal = true;

        $this->dispatch('lock-scroll');
    }

    // Open modal to edit an existing cart item
    public function editItem($id)
    {
        $item = Cart::get($id);
        if (!$item) {
            return;
        }

        $this->selectedProduct = Product::with(['options.values'])->findOrFail($item->id);

        // Prefill selected options using saved attributes
        $this->selectedOptions = [];
        $attributes = $item->attributes ? $item->attributes->toArray() : [];
        $savedOptions = $attributes['options'] ?? [];

        // If associative by optionId => [id,value,price_adjustment]
        foreach ($this->selectedProduct->options as $option) {
            $optId = $option->id;
            if (isset($savedOptions[$optId]) && isset($savedOptions[$optId]['id'])) {
                $this->selectedOptions[$optId] = $savedOptions[$optId]['id'];
            } else {
                // default to null (force user to choose if required)
                $this->selectedOptions[$optId] = null;
            }
        }

        $this->quantity = $item->quantity;
        $this->editingItemId = $id;
        $this->showOptionModal = true;
        $this->dispatch('lock-scroll');
    }
    public function increaseQty($id)
    {
        Cart::update($id, [
            'quantity' => [
                'relative' => true,
                'value' => 1
            ]
        ]);
        $this->refreshCart();
    }

    public function decreaseQty($id)
    {
        $item = Cart::get($id);
        if (!$item) return;

        if ($item->quantity <= 1) {
            // Store for confirmation and open modal
            $this->removingItemId = $id;
            $this->removingItemName = $item->name;
            $this->removeItemModal = true;
            $this->dispatch('lock-scroll');
            return;
        }

        Cart::update($id, [
            'quantity' => [
                'relative' => true,
                'value' => -1
            ]
        ]);
        $this->refreshCart();
    }

    public function removeItem($id)
    {
        Cart::remove($id);
        $this->refreshCart();
    }

    public function cancelRemove()
    {
        $this->removeItemModal = false;
        $this->removingItemId = null;
        $this->removingItemName = '';
        $this->dispatch('unlock-scroll');
    }

    // Quantity control inside modal
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

    // Add new item or update existing one based on $editingItemId
    public function addSelectedProductToCart()
    {
        if (!$this->selectedProduct) {
            return;
        }

        $price = $this->selectedProduct->price;
        $options = [];

        foreach ($this->selectedProduct->options as $option) {
            $valueId = $this->selectedOptions[$option->id] ?? null;
            if ($valueId) {
                $value = ProductOptionValue::find($valueId);
                if ($value) {
                    $options[$option->id] = [
                        'id' => $value->id,
                        'value' => $value->value,
                        'price_adjustment' => $value->price_adjustment,
                    ];
                    $price += $value->price_adjustment;
                }
            }
        }

        $attributes = [
            'options'     => $options,
            'base_price'  => $this->selectedProduct->price,
            'image'       => $this->selectedProduct->product_image ?? null,
            'description' => $this->selectedProduct->description ?? null,
            'category'    => $this->selectedProduct->category->name ?? null,
        ];

        if ($this->editingItemId) {
            // Update existing cart item
            Cart::update($this->editingItemId, [
                'price' => $price,
                'quantity' => [
                    'relative' => false,
                    'value' => $this->quantity,
                ],
                'attributes' => $attributes,
            ]);
        } else {
            // Add as new cart item
            Cart::add([
                'id' => $this->selectedProduct->id,
                'name' => $this->selectedProduct->name,
                'price' => $price,
                'quantity' => $this->quantity,
                'attributes' => $attributes,
            ]);
        }

        $this->reset(['selectedProduct', 'selectedOptions', 'quantity', 'showOptionModal', 'editingItemId']);
        $this->dispatch('unlock-scroll');
        $this->refreshCart();
        $this->dispatch('cart-updated');
    }

    // Computed property for modal total
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

    public function confirmRemove()
    {
        if ($this->removingItemId !== null) {
            Cart::remove($this->removingItemId);
            $this->refreshCart();
        }
        $this->cancelRemove();
    }

    public function clearCart()
    {
        Cart::clear();
        $this->refreshCart();
    }

    // No customer editing here; handled on OrderPage

    public function checkout()
    {
        if (Cart::isEmpty()) {
            session()->flash('error', 'Your cart is empty.');
            return;
        }
        $customerDetailId = session('customer_detail_id');
        if (!$customerDetailId) {
            session()->flash('error', 'Please enter your name and email before checkout.');
            return;
        }
        
        DB::transaction(function () {
            // Resolve tenant id robustly for Livewire requests, fallback to bound property
            $tenantId = tenant()?->id ?? request()->route('tenant') ?? $this->tenantId;
            $order = Order::create([
                'customer_detail_id' => session('customer_detail_id'),
                'total' => $this->grandTotal,
                'status' => 'pending',
                'tenant_id' => $tenantId,
            ]);

        foreach (Cart::getContent() as $item) {
            $options = $item->attributes ? $item->attributes->toArray() : null;

            // Remove 'image' if it exists
            if (is_array($options) && array_key_exists('image', $options)) {
                unset($options['image']);
            }


            OrderItem::create([
                'order_id'     => $order->id,
                'product_id'   => $item->id,
                'product_name' => $item->name,
                'unit_price'   => $item->price,
                'quantity'     => $item->quantity,
                'options'      => $options,
                'tenant_id'    => $tenantId ?? $this->tenantId,
            ]);
        }

            // Optionally store order id for receipt page later
            session(['last_order_id' => $order->id]);
        });

        $this->clearCart();
        session()->flash('success', 'Order placed successfully.');
        // Avoid flushing the entire session as it breaks Livewire
        // and clears authentication/csrf tokens. Forget only what we set.
        session()->forget(['customer_detail_id']);
    }

    public function render()
    {
        return view('livewire.cart-page', [
            'featuredProducts' => Product::where('featured', 1)->get(),
        ]);
    }
}
