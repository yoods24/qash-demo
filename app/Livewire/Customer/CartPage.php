<?php

namespace App\Livewire\Customer;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductOptionValue;
use Livewire\Component;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Darryldecode\Cart\Facades\CartFacade as Cart;
use App\Models\TenantNotification;
use App\Models\DiningTable;
use Illuminate\Support\Facades\Session;

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
    public $currentTable = null; // label for current dining table

    public function mount()
    {
        $this->tenantId = tenant()?->id ?? request()->route('tenant');
        $this->refreshCart();
        $this->syncCurrentTable();
    }

    public function refreshCart()
    {
        $this->items = Cart::getContent();
        $this->total = Cart::getTotal();
        // Always include software/service fee when there is at least one item
        $hasItems = $this->items && count($this->items) > 0;
        $this->grandTotal = $this->total + ($hasItems ? ($this->softwareService ?? 0) : 0);
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
            'estimated_seconds' => (int) ($this->selectedProduct->estimated_seconds ?? 0),
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
        // Validate cart items availability
        $insufficient = [];
        foreach (Cart::getContent() as $item) {
            $p = Product::find($item->id);
            if (!$p || ($p->active ?? 1) != 1) {
                session()->flash('error', 'Some items are unavailable and were removed.');
                Cart::remove($item->id);
                continue;
            }
            if ((int)($p->stock_qty ?? 0) < (int) $item->quantity) {
                $insufficient[] = $p->name ?? ('ID '.$p->id);
            }
        }
        if (!empty($insufficient)) {
            session()->flash('error', 'Insufficient stock for: '.implode(', ', $insufficient));
            return;
        }
        if (Cart::isEmpty()) {
            return;
        }
        
        DB::transaction(function () {
            // Resolve tenant id robustly for Livewire requests, fallback to bound property
            $tenantId = tenant()?->id ?? request()->route('tenant') ?? $this->tenantId;
            // Generate tenant-scoped reference number (e.g., DEM-XXXXXXXXXX)
            $tenantCode = strtoupper(substr((string)($tenantId ?? ''), 0, 3));
            $rand = strtoupper(substr(bin2hex(random_bytes(8)), 0, 10));
            $reference = ($tenantCode ?: 'REF') . '-' . $rand;

            // Compute expected total seconds from cart
            $expectedSeconds = 0;
            foreach (Cart::getContent() as $ci) {
                $est = (int) (($ci->attributes['estimated_seconds'] ?? 0));
                $expectedSeconds += $est * (int)$ci->quantity;
            }

            $order = Order::create([
                'reference_no'    => $reference,
                'customer_detail_id' => session('customer_detail_id'),
                'total' => $this->grandTotal,
                // Order pipeline status (KDS uses 'confirmed')
                'status' => 'confirmed',
                // Payment: mark as paid in development until gateway is integrated
                'payment_status' => 'paid',
                'tenant_id' => $tenantId,
                'confirmed_at' => now(),
                'expected_seconds_total' => $expectedSeconds,
            ]);

        foreach (Cart::getContent() as $item) {
            $options = $item->attributes ? $item->attributes->toArray() : null;

            // Remove 'image' if it exists
            if (is_array($options) && array_key_exists('image', $options)) {
                unset($options['image']);
            }
            // Create order item
            $orderItem = OrderItem::create([
                'order_id'     => $order->id,
                'product_id'   => $item->id,
                'product_name' => $item->name,
                'unit_price'   => $item->price,
                'quantity'     => $item->quantity,
                'options'      => $options,
                'estimate_seconds' => (int) ($options['estimated_seconds'] ?? 0),
                'tenant_id'    => $tenantId ?? $this->tenantId,
            ]);

            // Reduce product stock safely
            $prod = Product::where('tenant_id', $tenantId)->lockForUpdate()->find($item->id);
            if ($prod) {
                // Double-check in-transaction quantity
                if ((int)$prod->stock_qty < (int)$item->quantity) {
                    throw new \RuntimeException('Insufficient stock for '.$prod->name.' during checkout.');
                }
                $prod->decrement('stock_qty', (int)$item->quantity);
            }
        }

            // Optionally store order id for receipt page later
            session(['last_order_id' => $order->id]);

            // Create a tenant-scoped notification (non-blocking)
            try {
                $itemCount = Cart::getTotalQuantity();
                TenantNotification::create([
                    'tenant_id'   => $tenantId,
                    'type'        => 'order',
                    'title'       => 'New Order Created',
                    'item_id'     => $order->id,
                    'description' => 'Order #' . $order->id . ' placed with ' . $itemCount . ' item' . ($itemCount == 1 ? '' : 's') . '.',
                    'route_name'  => 'backoffice.order.view',
                    'route_params' => json_encode(['order' => $order->id, 'tenant' => $tenantId])
                ]);
            } catch (\Throwable $e) {
                // notifications should not block checkout
            }
        });

        $this->clearCart();
        session()->flash('success', 'Order placed successfully.');
        // Avoid flushing the entire session as it breaks Livewire
        // and clears authentication/csrf tokens. Forget only what we set.
        session()->forget(['customer_detail_id']);
    }

    public function render()
    {
        return view('livewire.customer.cart-page', [
            'featuredProducts' => Product::where('featured', 1)
                ->where('active', 1)
                ->where('stock_qty', '>', 0)
                ->get(),
        ]);
    }
}
