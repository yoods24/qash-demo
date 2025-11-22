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
use App\Support\CartItemIdentifier;
use App\Services\OrderTaxCalculator;
use App\Support\PriceAfterDiscount;
use App\Services\Order\OrderCreationService;

class CartPage extends Component
{
    private const ACTIVE_ORDER_SESSION_KEY = 'active_order_id';

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
    public $note = '';
    public array $taxPreview = [];
    public $currentTable = null; // label for current dining table
    public $subtotalBeforeDiscount = 0;
    public $discountTotal = 0;
    protected OrderTaxCalculator $orderTaxCalculator;

    public function boot(OrderTaxCalculator $orderTaxCalculator): void
    {
        $this->orderTaxCalculator = $orderTaxCalculator;
    }

    public function mount()
    {
        $this->tenantId = tenant()?->id ?? request()->route('tenant');
        $this->refreshCart();
        $this->syncCurrentTable();
    }

    public function refreshCart()
    {
        $this->items = Cart::getContent();
        $summary = $this->summarizeCart($this->items);
        $this->subtotalBeforeDiscount = $summary['subtotal'];
        $this->discountTotal = $summary['discount'];
        $this->total = $summary['final'];

        $calculation = $this->orderTaxCalculator->calculate($this->tenantId, $this->total);

        $this->taxPreview = [
            'total_tax' => $calculation->totalTax,
            'lines' => $calculation->lines->toArray(),
        ];

        $this->grandTotal = $calculation->grandTotal;
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
        $this->selectedProduct = Product::with(['options.values', 'category'])->findOrFail($productId);

        // reset options
        $this->selectedOptions = [];
        foreach ($this->selectedProduct->options as $option) {
            $this->selectedOptions[$option->id] = null;
        }

        $this->selectedProduct->setAttribute(
            'discount_details',
            PriceAfterDiscount::calculate($this->selectedProduct, $this->resolveTenantId())
        );

        $this->quantity = 1;
        $this->note = '';
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

        $productId = CartItemIdentifier::extractProductId($item) ?? $item->id;
        $this->selectedProduct = Product::with(['options.values', 'category'])->findOrFail($productId);

        // Prefill selected options using saved attributes
        $this->selectedOptions = [];
        $attributes = $item->attributes ? $item->attributes->toArray() : [];
        $savedOptions = $attributes['options'] ?? [];
        $this->note = $attributes['note'] ?? '';

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

        $this->selectedProduct->setAttribute(
            'discount_details',
            PriceAfterDiscount::calculate($this->selectedProduct, $this->resolveTenantId())
        );

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

        $this->selectedProduct->loadMissing('category');

        $payload = $this->buildCartPayload(
            $this->selectedProduct,
            $options,
            $price,
            $this->quantity,
            $this->note
        );

        if ($this->editingItemId) {
            Cart::remove($this->editingItemId);
            $this->editingItemId = null;
        }

        Cart::add($payload);

        $this->reset(['selectedProduct', 'selectedOptions', 'quantity', 'showOptionModal', 'editingItemId', 'note']);
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
        $tenantId = $this->resolveTenantId();

        if ($pendingOrder = $this->getPendingOrder($tenantId)) {
            return redirect()->route('payment.show', ['tenant' => $tenantId, 'order' => $pendingOrder]);
        }

        $insufficient = $this->validateCartStock($tenantId);
        if (! empty($insufficient)) {
            session()->flash('error', 'Insufficient stock for: ' . implode(', ', $insufficient));
            return;
        }

        if (Cart::isEmpty()) {
            return;
        }

        $itemCount = Cart::getTotalQuantity();
        $summary = $this->summarizeCart();

        $order = DB::transaction(function () use ($tenantId, $customerDetailId, $summary) {
            $reference = $this->generateReference($tenantId);
            $expectedSeconds = $this->calculateExpectedSeconds();
            $calculation = $this->orderTaxCalculator->calculate($tenantId, $summary['final']);

            $order = Order::create([
                'reference_no' => $reference,
                'customer_detail_id' => $customerDetailId,
                'total' => $calculation->grandTotal,
                'subtotal' => $calculation->subtotal,
                'total_tax' => $calculation->totalTax,
                'grand_total' => $calculation->grandTotal,
                'status' => 'waiting_for_payment',
                'payment_status' => 'pending',
                'payment_channel' => null,
                'tenant_id' => $tenantId,
                'expected_seconds_total' => $expectedSeconds,
                'source' => 'qr',
                'order_type' => Session::has('dining_table_id') ? 'dine-in' : 'takeaway',
            ]);

            foreach (Cart::getContent() as $item) {
                $attributes = $item->attributes ? $item->attributes->toArray() : [];
                $options = $attributes;

                foreach (['image', 'product_id', 'raw_price', 'discount_id', 'discount_amount', 'discount_name', 'discount_badge', 'final_price', 'note'] as $key) {
                    unset($options[$key]);
                }

                $productId = CartItemIdentifier::extractProductId($item) ?? $item->id;
                $unitPrice = (float) ($attributes['raw_price'] ?? $item->price);
                $finalPrice = (float) ($attributes['final_price'] ?? $item->price);
                $discountAmount = (float) ($attributes['discount_amount'] ?? max($unitPrice - $finalPrice, 0));
                $discountId = $attributes['discount_id'] ?? null;

                OrderItem::create([
                    'order_id'     => $order->id,
                    'product_id'   => $productId,
                    'product_name' => $item->name,
                    'unit_price'   => $unitPrice,
                    'final_price'  => $finalPrice,
                    'discount_amount' => $discountAmount,
                    'discount_id'  => $discountId,
                    'quantity'     => $item->quantity,
                    'options'      => $options,
                    'estimate_seconds' => (int) ($attributes['estimated_seconds'] ?? 0),
                    'special_instructions' => $attributes['note'] ?? null,
                    'tenant_id'    => $tenantId ?? $this->tenantId,
                ]);
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

            app(OrderCreationService::class)->handleDiscountUsage($order);

            session(['last_order_id' => $order->id]);

            return $order;
        });

        try {
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

        $this->clearCart();
        session([self::ACTIVE_ORDER_SESSION_KEY => $order->id]);
        session()->flash('success', 'Order created. Please choose a payment method.');
        session()->forget(['customer_detail_id']);

        return redirect()->route('payment.show', ['tenant' => $tenantId, 'order' => $order]);
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

    private function resolveTenantId(): ?string
    {
        return tenant()?->id ?? request()->route('tenant') ?? $this->tenantId;
    }

    private function getPendingOrder(?string $tenantId): ?Order
    {
        $pendingId = session(self::ACTIVE_ORDER_SESSION_KEY);
        if (! $pendingId || ! $tenantId) {
            return null;
        }

        $order = Order::where('tenant_id', $tenantId)->find($pendingId);

        if ($order && $order->payment_status === 'pending') {
            return $order;
        }

        session()->forget(self::ACTIVE_ORDER_SESSION_KEY);

        return null;
    }

    private function generateReference(?string $tenantId): string
    {
        $tenantCode = strtoupper(substr((string) ($tenantId ?? ''), 0, 3));
        $rand = strtoupper(substr(bin2hex(random_bytes(8)), 0, 10));
        return ($tenantCode ?: 'REF') . '-' . $rand;
    }

    private function calculateExpectedSeconds(): int
    {
        $expectedSeconds = 0;
        foreach (Cart::getContent() as $ci) {
            $est = (int) (($ci->attributes['estimated_seconds'] ?? 0));
            $expectedSeconds += $est * (int) $ci->quantity;
        }

        return $expectedSeconds;
    }

    private function validateCartStock(?string $tenantId): array
    {
        $insufficient = [];

        foreach (Cart::getContent() as $item) {
            $productId = CartItemIdentifier::extractProductId($item);
            $product = Product::where('tenant_id', $tenantId)->find($productId);

            if (! $product || ($product->active ?? 1) != 1) {
                session()->flash('error', 'Some items are unavailable and were removed.');
                Cart::remove($item->id);
                continue;
            }

            if ((int) ($product->stock_qty ?? 0) < (int) $item->quantity) {
                $insufficient[] = $product->name ?? ('ID ' . $product->id);
            }
        }

        return $insufficient;
    }

    private function buildCartPayload(Product $product, array $options, float $price, int $quantity, string $note = ''): array
    {
        $tenantId = $this->resolveTenantId();
        $pricing = PriceAfterDiscount::calculate($product, $tenantId, $price);

        $attributes = [
            'product_id' => $product->id,
            'options' => $options,
            'base_price' => $product->price,
            'raw_price' => $price,
            'image' => $product->product_image ?? null,
            'description' => $product->description ?? null,
            'category' => $product->category->name ?? null,
            'estimated_seconds' => (int) ($product->estimated_seconds ?? 0),
            'note' => $note,
            'discount_id' => $pricing['discount_id'],
            'discount_amount' => $pricing['discount_amount'],
            'discount_name' => $pricing['discount_name'],
            'discount_badge' => $pricing['badge'],
            'final_price' => $pricing['final_price'],
        ];

        return [
            'id' => CartItemIdentifier::make($product->id, $options),
            'name' => $product->name,
            'price' => $pricing['final_price'],
            'quantity' => $quantity,
            'attributes' => $attributes,
        ];
    }

    private function summarizeCart($items = null): array
    {
        $items = $items ?? Cart::getContent();
        $subtotal = 0.0;
        $discount = 0.0;

        foreach ($items as $item) {
            $raw = (float) ($item->attributes['raw_price'] ?? $item->price);
            $subtotal += $raw * (int) $item->quantity;

            $lineDiscount = (float) ($item->attributes['discount_amount'] ?? 0);
            $discount += $lineDiscount * (int) $item->quantity;
        }

        $final = max($subtotal - $discount, 0);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'final' => $final,
        ];
    }
}
