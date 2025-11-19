<?php

declare(strict_types=1);

namespace App\Livewire\Backoffice;

use App\Models\Category;
use App\Models\CustomerDetail;
use App\Models\DiningTable;
use App\Models\Product;
use App\Models\ProductOptionValue;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Darryldecode\Cart\Facades\CartFacade as Cart;
use Livewire\Attributes\Validate;
use Livewire\Component;
use App\Support\CartItemIdentifier;
use App\Services\OrderTaxCalculator;

class PosPage extends Component
{
    public string|int|null $tenantId = null;

    // Catalog state
    public string $search = '';
    public ?int $categoryId = null;
    protected OrderTaxCalculator $orderTaxCalculator;

    // POS inputs
    public ?int $customerId = null;
    public string $orderType = 'dine-in'; // 'dine-in' | 'takeaway'
    public ?int $tableId = null;

    // Modal: new customer
    #[Validate('required|string|min:2|max:120')]
    public string $newCustomerName = '';
    #[Validate('nullable|email|max:160')]
    public ?string $newCustomerEmail = null;
    #[Validate('nullable|in:male,female,other')]
    public ?string $newCustomerGender = null;

    public function boot(OrderTaxCalculator $orderTaxCalculator): void
    {
        if ($this->tenantId === null) {
            $this->tenantId = request()->route('tenant') ?? (function_exists('tenant') ? tenant('id') : null);
        }
        $this->orderTaxCalculator = $orderTaxCalculator;

    }

    // Product modal state
    public ? Product $selectedProduct = null;
    public array $selectedOptions = [];
    public int $quantity = 1;
    public bool $showOptionModal = false;
    public string $note = '';
    public array $suggestedAddons = [];
    public array $addonQty = []; // [productId => qty]

    public function showProductOptions(int $productId): void
    {
        $this->selectedProduct = Product::with(['options.values'])->findOrFail($productId);
        $this->selectedOptions = [];
        foreach ($this->selectedProduct->options as $opt) {
            $this->selectedOptions[$opt->id] = null;
        }
        $this->quantity = 1;
        $this->note = '';
        // Suggest two random addons (serialize to plain arrays for Livewire rehydration)
        $this->suggestedAddons = Product::query()
            ->when($this->tenantId, fn($q) => $q->where('tenant_id', $this->tenantId))
            ->where('id', '!=', $productId)
            ->inRandomOrder()
            ->limit(2)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => (int) $p->id,
                    'name' => (string) $p->name,
                    'price' => (float) ($p->price ?? 0),
                    'image_url' => $p->product_image_url,
                ];
            })
            ->all();
        $this->addonQty = [];
        foreach ($this->suggestedAddons as $ad) { $this->addonQty[$ad['id']] = 0; }
        $this->showOptionModal = true;
        $this->dispatch('lock-scroll');
        $this->dispatch('pos-open-product-modal');
    }

    public function closeOptionModal(): void
    {
        $this->showOptionModal = false;
        $this->dispatch('unlock-scroll');
        $this->dispatch('pos-close-product-modal');
    }

    public function incrementQuantity(): void { $this->quantity++; }
    public function decrementQuantity(): void { if ($this->quantity > 1) $this->quantity--; }
    public function incAddon(int $pid): void { $this->addonQty[$pid] = max(0, (int)($this->addonQty[$pid] ?? 0) + 1); }
    public function decAddon(int $pid): void { $this->addonQty[$pid] = max(0, (int)($this->addonQty[$pid] ?? 0) - 1); }

    public function addSelectedProductToCart(): void
    {
        if (!$this->selectedProduct) return;

        $price = (float) ($this->selectedProduct->price ?? 0);
        $options = [];
        foreach ($this->selectedOptions as $optionId => $valueId) {
            if ($valueId) {
                $v = ProductOptionValue::find($valueId);
                if ($v) {
                    $options[$optionId] = [
                        'id' => $v->id,
                        'value' => $v->value,
                        'price_adjustment' => (float) $v->price_adjustment,
                    ];
                    $price += (float) $v->price_adjustment;
                }
            }
        }

        // Add main line item
        Cart::add([
            'id' => CartItemIdentifier::make($this->selectedProduct->id, $options),
            'name' => $this->selectedProduct->name,
            'price' => $price,
            'quantity' => $this->quantity,
            'attributes' => [
                'product_id' => $this->selectedProduct->id,
                'options' => $options,
                'note' => $this->note,
                'base_price' => (float) ($this->selectedProduct->price ?? 0),
                'estimated_seconds' => (int) ($this->selectedProduct->estimated_seconds ?? 0),
            ],
        ]);

        // Add-ons as separate items
        foreach ($this->addonQty as $pid => $qty) {
            $qty = (int) $qty;
            if ($qty <= 0) continue;
            $p = Product::find($pid);
            if (!$p) continue;
            Cart::add([
                'id' => CartItemIdentifier::make($p->id, ['parent' => $this->selectedProduct->id, 'is_addon' => true]),
                'name' => $p->name,
                'price' => (float) ($p->price ?? 0),
                'quantity' => $qty,
                'attributes' => [
                    'product_id' => $p->id,
                    'is_addon' => true,
                    'parent_product_id' => $this->selectedProduct->id,
                    'estimated_seconds' => (int) ($p->estimated_seconds ?? 0),
                ],
            ]);
        }

        $this->showOptionModal = false;
        $this->dispatch('unlock-scroll');
        $this->dispatch('pos-close-product-modal');
        $this->reset(['selectedProduct','selectedOptions','quantity','note','suggestedAddons','addonQty']);
        $this->dispatch('cart-updated');
    }

    public function getModalTotalProperty(): float
    {
        if (!$this->selectedProduct) return 0.0;
        $t = (float) ($this->selectedProduct->price ?? 0);
        foreach (($this->selectedProduct->options ?? []) as $opt) {
            $valueId = $this->selectedOptions[$opt->id] ?? null;
            if ($valueId) {
                $val = $opt->values->firstWhere('id', $valueId);
                if ($val) $t += (float) $val->price_adjustment;
            }
        }
        $t = $t * max(1, $this->quantity);
        // include add-ons preview
        foreach ($this->suggestedAddons as $ad) {
            $qty = (int) ($this->addonQty[$ad['id']] ?? 0);
            if ($qty > 0) $t += (float) ($ad['price'] ?? 0) * $qty;
        }
        return $t;
    }

    public function mount(): void
    {
        // Default selection if only one category exists (optional behavior)
        // no-op for now
    }

    public function updatedOrderType(string $value): void
    {
        if ($value !== 'dine-in') {
            // Clear table for takeaway
            $this->tableId = null;
        }
    }

    public function addCustomer(): void
    {
        $this->validate();

        $customer = CustomerDetail::create([
            'tenant_id' => $this->tenantId,
            'name' => $this->newCustomerName,
            'email' => $this->newCustomerEmail,
            'gender' => $this->newCustomerGender,
        ]);

        $this->customerId = (int) $customer->id;
        $this->newCustomerName = '';
        $this->newCustomerEmail = null;
        $this->newCustomerGender = null;

        // Close the modal on the frontend
        $this->dispatch('pos-close-add-customer');
        // Ensure any loading overlays are hidden
        $this->dispatch('overlay-hide');
        $this->dispatch('pos-flash', type: 'success', message: 'Customer added');
    }

    public function getCustomersProperty()
    {
        return CustomerDetail::query()
            ->when($this->tenantId, fn ($q) => $q->where('tenant_id', $this->tenantId))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getTablesProperty()
    {
        return DiningTable::query()
            ->when($this->tenantId, fn ($q) => $q->where('tenant_id', $this->tenantId))
            ->orderBy('label')
            ->get(['id', 'label', 'status']);
    }

    public function getCategoriesProperty()
    {
        return Category::query()
            ->when($this->tenantId, fn ($q) => $q->where('tenant_id', $this->tenantId))
            ->orderBy('name')
            ->get(['id', 'name', 'image_url']);
    }

    public function getProductsProperty()
    {
        return Product::query()
            ->when($this->tenantId, fn ($q) => $q->where('tenant_id', $this->tenantId))
            ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
            ->when(strlen($this->search) > 0, function ($q) {
                $s = '%' . trim($this->search) . '%';
                $q->where(function ($qq) use ($s) {
                    $qq->where('name', 'like', $s)
                       ->orWhere('description', 'like', $s);
                });
            })
            ->latest('id')
            ->limit(60)
            ->get();
    }

    // Cart helpers

    public function getCartItemsProperty()
    {
        return Cart::getContent();
    }

    public function getCartSummaryProperty(): array
    {
        $subtotal = 0.0;
        foreach (Cart::getContent() as $ci) {
            $subtotal += (float)$ci->price * (int)$ci->quantity;
        }
        $discount = 0.0; // future: coupons/percentage
        $calculation = $this->orderTaxCalculator->calculate($this->tenantId, max(0, $subtotal - $discount));
        $total = $calculation->grandTotal;
        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax_lines' => $calculation->lines->toArray(),
            'total_tax' => $calculation->totalTax,
            'total' => $total,
        ];
    }

    public function increaseItem(int|string $id): void
    {
        Cart::update($id, [
            'quantity' => [ 'relative' => true, 'value' => 1 ]
        ]);
    }

    public function decreaseItem(int|string $id): void
    {
        $item = Cart::get($id);
        if (!$item) return;
        if ((int)$item->quantity <= 1) {
            Cart::remove($id);
        } else {
            Cart::update($id, [
                'quantity' => [ 'relative' => true, 'value' => -1 ]
            ]);
        }
    }

    public function removeItem(int|string $id): void
    {
        Cart::remove($id);
    }

    public function selectCategory(?int $id): void
    {
        $this->categoryId = $id;
        // Notify frontend to update slider active state without re-rendering it
        $this->dispatch('pos-category-updated', id: $id);
    }

    public function checkout(): void
    {
        if (Cart::isEmpty()) {
            $this->dispatch('pos-flash', type: 'warning', message: 'Cart is empty.');
            return;
        }
        if (!$this->customerId) {
            $this->dispatch('pos-flash', type: 'warning', message: 'Select a customer first.');
            return;
        }
        if ($this->orderType === 'dine-in' && !$this->tableId) {
            $this->dispatch('pos-flash', type: 'warning', message: 'Please pick a table for dine-in.');
            return;
        }

        DB::transaction(function () {
            $tenantId = $this->tenantId;
            $tenantCode = strtoupper(substr((string)($tenantId ?? ''), 0, 3));
            $rand = strtoupper(substr(bin2hex(random_bytes(8)), 0, 10));
            $reference = ($tenantCode ?: 'REF') . '-' . $rand;

            // expected total seconds from cart
            $expectedSeconds = 0; $cartSubtotal = 0;
            foreach (Cart::getContent() as $ci) {
                $cartSubtotal += (float)$ci->price * (int)$ci->quantity;
                $expectedSeconds += (int) (($ci->attributes['estimated_seconds'] ?? 0)) * (int)$ci->quantity;
            }
            $calculation = $this->orderTaxCalculator->calculate($tenantId, $cartSubtotal);

            $order = Order::create([
                'reference_no' => $reference,
                'customer_detail_id' => $this->customerId,
                'total' => $calculation->grandTotal,
                'subtotal' => $calculation->subtotal,
                'total_tax' => $calculation->totalTax,
                'grand_total' => $calculation->grandTotal,
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'payment_channel' => 'cash',
                'paid_at' => now(),
                'tenant_id' => $tenantId,
                'confirmed_at' => now(),
                'expected_seconds_total' => $expectedSeconds,
                'source' => 'pos',
                'order_type' => $this->orderType,
            ]);

            foreach (Cart::getContent() as $item) {
                $attributes = $item->attributes ? $item->attributes->toArray() : [];
                $options = $attributes; // include options structure
                unset($options['image'], $options['product_id']);
                $productId = CartItemIdentifier::extractProductId($item) ?? $item->id;
                OrderItem::create([
                    'tenant_id' => $tenantId,
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'product_name' => $item->name,
                    'unit_price' => (float) $item->price,
                    'quantity' => (int) $item->quantity,
                    'options' => $options,
                    'estimate_seconds' => (int) ($attributes['estimated_seconds'] ?? 0),
                    'special_instructions' => $attributes['note'] ?? null,
                ]);
                // Decrement stock
                if ($p = Product::where('tenant_id', $tenantId)->lockForUpdate()->find($productId)) {
                    $p->decrement('stock_qty', (int) $item->quantity);
                }
            }

            foreach ($calculation->lines as $line) {
                $order->taxLines()->create([
                    'tax_id' => $line['tax_id'],
                    'name' => $line['name'],
                    'type' => $line['type'],
                    'rate' => $line['rate'],
                    'amount' => $line['amount'],
                ]);
            }

            // Update customer + table association and table status if dine-in
            if ($this->orderType === 'dine-in' && $this->tableId) {
                CustomerDetail::where('tenant_id', $tenantId)->where('id', $this->customerId)
                    ->update(['dining_table_id' => $this->tableId]);
                DiningTable::where('tenant_id', $tenantId)->where('id', $this->tableId)
                    ->update(['status' => 'occupied']);
            }
        });

        Cart::clear();
        $this->dispatch('pos-flash', type: 'success', message: 'Order placed.');
    }

    /**
     * Quick add a product to the POS cart (no options modal).
     */
    public function addProductToCart(int $productId): void
    {
        $p = Product::find($productId);
        if (!$p) {
            $this->dispatch('notify', type: 'error', message: 'Product not found');
            return;
        }

        Cart::add([
            'id' => CartItemIdentifier::make($p->id),
            'name' => (string) $p->name,
            'price' => (float) ($p->price ?? 0),
            'quantity' => 1,
            'attributes' => [
                'product_id' => $p->id,
                'base_price' => (float) ($p->price ?? 0),
                'estimated_seconds' => (int) ($p->estimated_seconds ?? 0),
            ],
        ]);

        $this->dispatch('cart-updated');
        $this->dispatch('notify', type: 'success', message: 'Added to cart');
    }

    public function render()
    {
        return view('livewire.backoffice.pos-page');
    }
}
