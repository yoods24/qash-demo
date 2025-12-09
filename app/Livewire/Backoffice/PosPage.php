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
use App\Models\TenantNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Darryldecode\Cart\Facades\CartFacade as Cart;
use Livewire\Attributes\Validate;
use Livewire\Component;
use App\Support\CartItemIdentifier;
use App\Services\OrderTaxCalculator;
use App\Services\Customer\DiscountFetcher;
use App\Jobs\SendInvoiceEmailJob;
use App\Support\PriceAfterDiscount;

class PosPage extends Component
{
    public string|int|null $tenantId = null;

    // Catalog state
    public string $search = '';
    public ?int $categoryId = null;
    public bool $productsLoaded = false;
    protected OrderTaxCalculator $orderTaxCalculator;
    protected DiscountFetcher $discountFetcher;

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

    // Payment modal
    public bool $showPaymentModal = false;
    public string $paymentMethod = 'cash';
    public string $cashReceivedFormatted = '';
    public ?float $cashReceivedNumeric = null;
    public bool $showCustomerSearch = false;
    public string $customerSearch = '';
    public array $customerSearchResults = [];
    public bool $showReceiptModal = false;
    public ?array $receiptData = null;

    public function boot(OrderTaxCalculator $orderTaxCalculator, DiscountFetcher $discountFetcher): void
    {
        if ($this->tenantId === null) {
            $this->tenantId = request()->route('tenant') ?? (function_exists('tenant') ? tenant('id') : null);
        }
        $this->orderTaxCalculator = $orderTaxCalculator;
        $this->discountFetcher = $discountFetcher;

    }

    // Product modal state
    public ?Product $selectedProduct = null;
    public array $selectedOptions = [];
    public int $quantity = 1;
    public bool $showOptionModal = false;
    public string $note = '';
    public ?string $editingItemId = null;

    public function showProductOptions(int $productId): void
    {
        $this->selectedProduct = Product::with(['options.values'])->findOrFail($productId);
        $this->selectedProduct = $this->applyDiscount($this->selectedProduct);
        $this->selectedOptions = [];
        foreach ($this->selectedProduct->options as $opt) {
            $this->selectedOptions[$opt->id] = null;
        }
        $this->quantity = 1;
        $this->note = '';
        $this->editingItemId = null;
        $this->showOptionModal = true;
        $this->dispatch('lock-scroll');
        $this->dispatch('pos-open-product-modal');
    }

    public function closeOptionModal(): void
    {
        $this->showOptionModal = false;
        $this->dispatch('unlock-scroll');
        $this->dispatch('pos-close-product-modal');
        $this->editingItemId = null;
    }

    public function incrementQuantity(): void { $this->quantity++; }
    public function decrementQuantity(): void { if ($this->quantity > 1) $this->quantity--; }

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

        $pricing = PriceAfterDiscount::calculate($this->selectedProduct, $this->resolveTenantId(), $price, $this->discountFetcher);

        if ($this->editingItemId) {
            Cart::remove($this->editingItemId);
        }

        // Add main line item
        Cart::add([
            'id' => CartItemIdentifier::make($this->selectedProduct->id, $options),
            'name' => $this->selectedProduct->name,
            'price' => (float) ($pricing['final_price'] ?? $price),
            'quantity' => $this->quantity,
            'attributes' => [
                'product_id' => $this->selectedProduct->id,
                'options' => $options,
                'note' => $this->note,
                'base_price' => (float) ($this->selectedProduct->price ?? 0),
                'raw_price' => $price,
                'discount_id' => $pricing['discount_id'] ?? null,
                'discount_amount' => (float) ($pricing['discount_amount'] ?? 0),
                'discount_name' => $pricing['discount_name'] ?? null,
                'discount_badge' => $pricing['badge'] ?? null,
                'final_price' => (float) ($pricing['final_price'] ?? $price),
                'estimated_seconds' => (int) ($this->selectedProduct->estimated_seconds ?? 0),
            ],
        ]);

        $this->showOptionModal = false;
        $this->dispatch('unlock-scroll');
        $this->dispatch('pos-close-product-modal');
        $this->reset(['selectedProduct','selectedOptions','quantity','note','editingItemId']);
        $this->dispatch('cart-updated');
    }

    public function getModalTotalProperty(): float
    {
        if (!$this->selectedProduct) return 0.0;
        $raw = (float) ($this->selectedProduct->price ?? 0);
        foreach (($this->selectedProduct->options ?? []) as $opt) {
            $valueId = $this->selectedOptions[$opt->id] ?? null;
            if ($valueId) {
                $val = $opt->values->firstWhere('id', $valueId);
                if ($val) $raw += (float) $val->price_adjustment;
            }
        }
        $pricing = PriceAfterDiscount::calculate($this->selectedProduct, $this->resolveTenantId(), $raw, $this->discountFetcher);
        $total = (float) ($pricing['final_price'] ?? $raw) * max(1, $this->quantity);
        return $total;
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
        if (! $this->productsLoaded) {
            return collect();
        }

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
            ->get()
            ->map(fn (Product $product) => $this->applyDiscount($product));
    }

    public function loadProducts(): void
    {
        $this->productsLoaded = true;
    }

    // Cart helpers

    public function getCartItemsProperty()
    {
        return Cart::getContent();
    }

    public function getCartSummaryProperty(): array
    {
        $subtotal = 0.0;
        $discount = 0.0;
        foreach (Cart::getContent() as $ci) {
            $rawPrice = (float) ($ci->attributes['raw_price'] ?? $ci->price);
            $subtotal += $rawPrice * (int)$ci->quantity;
            $lineDiscount = (float) ($ci->attributes['discount_amount'] ?? 0);
            $discount += $lineDiscount * (int)$ci->quantity;
        }
        $net = max($subtotal - $discount, 0);
        $calculation = $this->orderTaxCalculator->calculate($this->tenantId, $net);
        $total = $calculation->grandTotal;
        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax_lines' => $calculation->lines->toArray(),
            'total_tax' => $calculation->totalTax,
            'total' => $total,
        ];
    }

    public function getTotalAmountProperty(): float
    {
        return (float) ($this->cartSummary['total'] ?? 0);
    }

    public function getCashChangeProperty(): float
    {
        return max(0, ($this->cashReceivedNumeric ?? 0) - $this->totalAmount);
    }

    public function getCanConfirmCashProperty(): bool
    {
        return ($this->cashReceivedNumeric ?? 0) >= $this->totalAmount;
    }

    public function getSelectedCustomerLabelProperty(): string
    {
        if (! $this->customerId) {
            return 'Walking Customer';
        }

        $customer = CustomerDetail::query()
            ->when($this->tenantId, fn ($q) => $q->where('tenant_id', $this->tenantId))
            ->find($this->customerId);

        if (! $customer) {
            return 'Walking Customer';
        }

        $email = $customer->email ? " ({$customer->email})" : '';

        return (string) $customer->name . $email;
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
        $this->openPaymentModal();
    }

    public function openPaymentModal(): void
    {
        if (! $this->validatePosOrder()) {
            return;
        }

        $this->paymentMethod = 'cash';
        $this->cashReceivedFormatted = '';
        $this->cashReceivedNumeric = null;
        $this->showPaymentModal = true;
        $this->showReceiptModal = false;
        $this->receiptData = null;
    }

    public function addCashDigit(string $digit): void
    {
        $current = preg_replace('/\D/', '', $this->cashReceivedFormatted) ?? '';
        $append = preg_replace('/\D/', '', $digit) ?? '';
        $numericString = $current . $append;

        if ($numericString === '') {
            $this->cashReceivedNumeric = null;
            $this->cashReceivedFormatted = '';
            return;
        }

        $amount = (int) $numericString;
        $this->cashReceivedNumeric = (float) $amount;
        $this->cashReceivedFormatted = number_format($amount, 0, ',', '.');
    }

    public function clearCashInput(): void
    {
        $this->cashReceivedFormatted = '';
        $this->cashReceivedNumeric = null;
    }

    public function updatedCashReceivedFormatted(string $value): void
    {
        $numericString = preg_replace('/\D/', '', $value) ?? '';

        if ($numericString === '') {
            $this->cashReceivedNumeric = null;
            $this->cashReceivedFormatted = '';
            return;
        }

        $amount = (int) $numericString;
        $formatted = number_format($amount, 0, ',', '.');

        $this->cashReceivedNumeric = (float) $amount;

        if ($formatted !== $this->cashReceivedFormatted) {
            $this->cashReceivedFormatted = $formatted;
        }
    }

    public function confirmCashPayment(): void
    {
        if (! $this->canConfirmCash) {
            return;
        }

        $this->finalizePosOrder(
            paymentChannel: 'cash',
            receivedAmount: $this->cashReceivedNumeric,
            change: $this->cashChange
        );
    }

    public function confirmCashPaymentClient(string $amountInput): void
    {
        $numericString = preg_replace('/\D/', '', $amountInput) ?? '';
        $amount = $numericString === '' ? 0.0 : (float) $numericString;
        $roundedTotal = roundToIndoRupiahTotal($this->totalAmount);

        $this->cashReceivedNumeric = $amount;
        $this->cashReceivedFormatted = $numericString === ''
            ? ''
            : number_format((int) $amount, 0, ',', '.');

        if ($amount < $roundedTotal) {
            $this->dispatch('pos-flash', type: 'warning', message: 'Received amount is less than total.');
            return;
        }

        $this->finalizePosOrder(
            paymentChannel: 'cash',
            receivedAmount: $this->cashReceivedNumeric,
            change: max(0, $this->cashReceivedNumeric - $roundedTotal)
        );
    }

    public function confirmCardPayment(): void
    {
        $this->finalizePosOrder(paymentChannel: 'card');
    }

    public function confirmQrisPayment(): void
    {
        $this->finalizePosOrder(paymentChannel: 'qris');
    }

    public function openCustomerSearch(): void
    {
        $this->customerSearch = '';
        $this->customerSearchResults = [];
        $this->showCustomerSearch = true;
    }

    public function closeCustomerSearch(): void
    {
        $this->showCustomerSearch = false;
    }

    public function updatedCustomerSearch(string $value): void
    {
        $this->searchCustomers($value);
    }

    public function performCustomerSearch(): void
    {
        $this->searchCustomers($this->customerSearch);
    }

    public function selectCustomerFromSearch(int $customerId): void
    {
        $customer = CustomerDetail::query()
            ->when($this->tenantId, fn ($q) => $q->where('tenant_id', $this->tenantId))
            ->find($customerId);

        if (! $customer) {
            $this->dispatch('pos-flash', type: 'warning', message: 'Customer not found.');
            return;
        }

        $this->customerId = (int) $customer->id;
        $this->showCustomerSearch = false;
        $this->dispatch('pos-flash', type: 'success', message: 'Customer selected.');
    }

    private function searchCustomers(?string $term): void
    {
        $term = trim((string) $term);

        if (strlen($term) < 2) {
            $this->customerSearchResults = [];
            return;
        }

        $results = CustomerDetail::query()
            ->when($this->tenantId, fn ($q) => $q->where('tenant_id', $this->tenantId))
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', '%' . $term . '%')
                    ->orWhere('email', 'like', '%' . $term . '%');
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'email']);

        $this->customerSearchResults = $results->map(fn (CustomerDetail $c) => [
            'id' => $c->id,
            'name' => $c->name,
            'email' => $c->email,
        ])->toArray();
    }

    private function finalizePosOrder(string $paymentChannel, ?float $receivedAmount = null, ?float $change = null): void
    {
        if (! $this->validatePosOrder()) {
            $this->showPaymentModal = false;
            return;
        }

        $cartItems = Cart::getContent();
        $itemCount = $cartItems->sum(fn ($ci) => (int) $ci->quantity);
        $receiptOrder = null;

        $order = DB::transaction(function () use ($paymentChannel, $cartItems, &$receiptOrder) {
            $tenantId = $this->resolveTenantId();
            $tenantCode = strtoupper(substr((string)($tenantId ?? ''), 0, 3));
            $rand = strtoupper(substr(bin2hex(random_bytes(8)), 0, 10));
            $reference = ($tenantCode ?: 'REF') . '-' . $rand;

            $expectedSeconds = 0;
            $cartSubtotal = 0;

            foreach ($cartItems as $ci) {
                $cartSubtotal += (float) $ci->price * (int) $ci->quantity;
                $expectedSeconds += (int) (($ci->attributes['estimated_seconds'] ?? 0)) * (int) $ci->quantity;
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
                'payment_channel' => $paymentChannel,
                'paid_at' => now(),
                'tenant_id' => $tenantId,
                'confirmed_at' => now(),
                'expected_seconds_total' => $expectedSeconds,
                'source' => 'pos',
                'order_type' => $this->orderType,
            ]);

            foreach ($cartItems as $item) {
                $attributes = $item->attributes ? $item->attributes->toArray() : [];
                $options = $attributes;
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

            if ($this->orderType === 'dine-in' && $this->tableId) {
                CustomerDetail::where('tenant_id', $tenantId)->where('id', $this->customerId)
                    ->update(['dining_table_id' => $this->tableId]);
                DiningTable::where('tenant_id', $tenantId)->where('id', $this->tableId)
                    ->update(['status' => 'occupied']);
            } elseif ($this->orderType === 'takeaway') {
                CustomerDetail::where('tenant_id', $tenantId)->where('id', $this->customerId)
                    ->update(['dining_table_id' => null]);
            }

            $receiptOrder = Order::with(['items', 'taxLines', 'customerDetail.diningTable'])
                ->find($order->id);

            return $order;
        });

        if ($order) {
            $this->persistCashAmounts($order, $receivedAmount, $change);
            try {
                TenantNotification::create([
                    'tenant_id' => $order->tenant_id,
                    'type' => 'order',
                    'title' => 'New Order Created',
                    'item_id' => $order->id,
                    'description' => 'Order #' . $order->id . ' placed with ' . $itemCount . ' item' . ($itemCount === 1 ? '' : 's') . '.',
                    'route_name' => 'backoffice.order.view',
                    'route_params' => json_encode(['order' => $order->id, 'tenant' => $order->tenant_id]),
                ]);
            } catch (\Throwable $e) {
                // notifications should not block checkout
            }
            $this->prepareReceiptData($receiptOrder, $receivedAmount, $change);
        }

        $this->resetCart();
        $this->showPaymentModal = false;
        $this->paymentMethod = 'cash';
        $this->clearCashInput();
        $this->dispatch('pos-flash', type: 'success', message: 'Order completed successfully.');
    }

    private function prepareReceiptData(?Order $order, ?float $receivedAmount, ?float $change): void
    {
        if (! $order) {
            $this->receiptData = null;
            $this->showReceiptModal = false;
            return;
        }

        $items = $order->items->map(function (OrderItem $item) {
            $options = $item->options ?? [];
            if (is_array($options) && array_key_exists('options', $options)) {
                $options = $options['options'] ?? [];
            }

            return [
                'name' => $item->product_name,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'line_total' => (float) $item->unit_price * (int) $item->quantity,
                'options' => $options,
                'note' => $item->special_instructions,
            ];
        })->toArray();

        $taxLines = $order->taxLines->map(fn ($line) => [
            'name' => $line->name,
            'amount' => (float) $line->amount,
            'rate' => $line->rate,
        ])->toArray();

        $receivedRounded = $receivedAmount === null ? null : roundToIndoRupiahTotal($receivedAmount);
        $changeRounded = $change === null ? null : roundToIndoRupiahTotal($change);

        $this->receiptData = [
            'order_id' => $order->id,
            'reference' => $order->reference_no,
            'order_type' => $order->orderTypeLabel(),
            'paid_at' => optional($order->paid_at)->format('d-m-Y H:i'),
            'customer_name' => $order->customerDetail?->name,
            'customer_email' => $order->customerDetail?->email,
            'customer_table' => $order->customerDetail?->diningTable?->label,
            'items' => $items,
            'subtotal' => (float) $order->subtotal,
            'total_tax' => (float) $order->total_tax,
            'grand_total' => (float) $order->grand_total,
            'received' => $receivedRounded,
            'change' => $changeRounded,
            'tax_lines' => $taxLines,
        ];

        $this->showReceiptModal = true;
    }

    public function sendReceiptEmail(): void
    {
        $orderId = $this->receiptData['order_id'] ?? null;
        $email = $this->receiptData['customer_email'] ?? null;

        if (! $orderId) {
            $this->dispatch('pos-flash', type: 'warning', message: 'No receipt to send.');
            return;
        }

        if (! $email) {
            $this->dispatch('pos-flash', type: 'warning', message: 'Customer email is missing.');
            return;
        }

        SendInvoiceEmailJob::dispatch((int) $orderId, (string) $this->resolveTenantId());
        $this->dispatch('pos-flash', type: 'success', message: 'Receipt email queued.');
    }

    public function printReceipt(): void
    {
        if (! $this->receiptData) {
            $this->dispatch('pos-flash', type: 'warning', message: 'No receipt to print.');
            return;
        }

        $html = view('backoffice.pos.receipt-print', ['receipt' => $this->receiptData])->render();
        $this->dispatch('pos-print-receipt', html: $html);
    }

    public function closeReceiptModal(): void
    {
        $this->showReceiptModal = false;
        $this->receiptData = null;
    }

    private function validatePosOrder(): bool
    {
        if ($this->cartIsEmpty()) {
            $this->dispatch('pos-flash', type: 'warning', message: 'Cart is empty.');
            return false;
        }

        if (! $this->customerId) {
            $this->dispatch('pos-flash', type: 'warning', message: 'Select a customer first.');
            return false;
        }

        if ($this->orderType === 'dine-in' && ! $this->tableId) {
            $this->dispatch('pos-flash', type: 'warning', message: 'Please pick a table for dine-in.');
            return false;
        }

        return true;
    }

    private function persistCashAmounts(Order $order, ?float $receivedAmount = null, ?float $change = null): void
    {
        $updates = [];

        if (Schema::hasColumn($order->getTable(), 'received_amount')) {
            $updates['received_amount'] = $receivedAmount;
        }

        if (Schema::hasColumn($order->getTable(), 'change_amount')) {
            $updates['change_amount'] = $change;
        }

        if (! empty($updates)) {
            $order->forceFill($updates)->save();
        }
    }

    private function cartIsEmpty(): bool
    {
        return Cart::isEmpty();
    }

    private function resetCart(): void
    {
        Cart::clear();
        $this->dispatch('cart-updated');
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

        $pricing = PriceAfterDiscount::calculate($p, $this->resolveTenantId(), null, $this->discountFetcher);

        Cart::add([
            'id' => CartItemIdentifier::make($p->id),
            'name' => (string) $p->name,
            'price' => (float) ($pricing['final_price'] ?? ($p->price ?? 0)),
            'quantity' => 1,
            'attributes' => [
                'product_id' => $p->id,
                'base_price' => (float) ($p->price ?? 0),
                'raw_price' => (float) ($p->price ?? 0),
                'discount_id' => $pricing['discount_id'] ?? null,
                'discount_amount' => (float) ($pricing['discount_amount'] ?? 0),
                'discount_name' => $pricing['discount_name'] ?? null,
                'discount_badge' => $pricing['badge'] ?? null,
                'final_price' => (float) ($pricing['final_price'] ?? ($p->price ?? 0)),
                'estimated_seconds' => (int) ($p->estimated_seconds ?? 0),
            ],
        ]);

        $this->dispatch('cart-updated');
        $this->dispatch('notify', type: 'success', message: 'Added to cart');
    }

    public function editCartItem(int|string $id): void
    {
        $item = Cart::get($id);
        if (!$item) {
            return;
        }

        $productId = CartItemIdentifier::extractProductId($item) ?? ($item->attributes['product_id'] ?? $item->id);
        $product = Product::with(['options.values'])->find($productId);
        if (!$product) {
            return;
        }

        $this->selectedProduct = $this->applyDiscount($product);
        $this->selectedOptions = [];
        $attributes = $item->attributes ? $item->attributes->toArray() : [];
        $savedOptions = $attributes['options'] ?? [];
        foreach ($this->selectedProduct->options as $option) {
            $optId = $option->id;
            if (isset($savedOptions[$optId]['id'])) {
                $this->selectedOptions[$optId] = $savedOptions[$optId]['id'];
            } else {
                $this->selectedOptions[$optId] = null;
            }
        }

        $this->quantity = (int) $item->quantity;
        $this->note = (string) ($attributes['note'] ?? '');
        $this->editingItemId = (string) $id;
        $this->showOptionModal = true;
        $this->dispatch('lock-scroll');
        $this->dispatch('pos-open-product-modal');
    }

    private function resolveTenantId(): ?string
    {
        return $this->tenantId ? (string) $this->tenantId : null;
    }

    private function applyDiscount(Product $product): Product
    {
        $product->setAttribute('discount_details', PriceAfterDiscount::calculate($product, $this->resolveTenantId(), null, $this->discountFetcher));
        return $product;
    }

    public function render()
    {
        return view('livewire.backoffice.pos-page');
    }
}
