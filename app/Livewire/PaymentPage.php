<?php

namespace App\Livewire;

use App\Models\Order;
use App\Services\OrderStockService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Xendit\Configuration;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Invoice\CustomerObject;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice\InvoiceFee;
use Xendit\XenditSdkException;

class PaymentPage extends Component
{
    public Order $order;

    public ?string $selectedMethod = null;

    public string|int|null $tenantId = null;

    public bool $processing = false;

    protected OrderStockService $orderStockService;

    public function boot(OrderStockService $orderStockService): void
    {
        $this->orderStockService = $orderStockService;
    }

    public function mount(Order $order): void
    {
        $this->order = $order->loadMissing(['items', 'customerDetail', 'taxLines']);
        $this->tenantId = request()->route('tenant') ?? $order->tenant_id;
    }

    public function selectMethod(string $method): void
    {
        if (($this->order->status ?? null) === 'cancelled') {
            return;
        }

        $this->selectedMethod = in_array($method, ['cash', 'digital'], true) ? $method : null;
    }

    public function confirmPayment()
    {
        if ($this->processing || ! $this->selectedMethod) {
            return null;
        }

        if (($this->order->status ?? null) === 'cancelled') {
            session()->flash('error', 'This order has been cancelled. Please start a new order.');
            return null;
        }

        return $this->selectedMethod === 'cash'
            ? $this->payWithCash()
            : $this->startDigitalPayment();
    }

    public function cancelOrder()
    {
        if ($this->processing) {
            return null;
        }

        $this->processing = true;

        try {
            DB::transaction(function () {
                $order = $this->order->fresh();

                if (! $order) {
                    throw new \RuntimeException('Missing order instance.');
                }

                $order->loadMissing(['items', 'customerDetail', 'taxLines']);

                if ($order->payment_status === 'paid') {
                    throw new \RuntimeException('Paid orders cannot be cancelled.');
                }

                if ($order->status === 'cancelled') {
                    $this->order = $order;
                    return;
                }

                $order->forceFill([
                    'payment_status' => 'cancelled',
                    'status' => 'cancelled',
                    'payment_channel' => null,
                    'xendit_invoice_id' => null,
                    'xendit_invoice_url' => null,
                    'paid_at' => null,
                ])->save();

                $this->order = $order;
            });
        } catch (\Throwable $exception) {
            Log::error('Failed to cancel order via Livewire', [
                'order_id' => $this->order->id ?? null,
                'message' => $exception->getMessage(),
            ]);

            $this->processing = false;
            session()->flash('error', 'Unable to cancel the order. Please contact our staff.');
            return null;
        }

        session()->forget('active_order_id');
        session()->forget('last_order_id');

        $this->processing = false;
        session()->flash('success', 'Order cancelled. You can place a new order now.');

        return redirect()->route('customer.order', [
            'tenant' => $this->tenantId,
        ]);
    }

    protected function payWithCash()
    {
        $this->processing = true;

        try {
            DB::transaction(function () {
                $order = $this->order->fresh();

                if (! $order) {
                    throw new \RuntimeException('Missing order instance.');
                }

                $order->loadMissing(['items', 'customerDetail', 'taxLines']);
                $this->orderStockService->deduct($order);

                $order->forceFill([
                    'payment_status' => 'paid',
                    'payment_channel' => 'cash',
                    'status' => 'confirmed',
                    'paid_at' => now(),
                ])->save();

                $this->order = $order;
            });
        } catch (\Throwable $exception) {
            Log::error('Failed to confirm cash payment via Livewire', [
                'order_id' => $this->order->id,
                'message' => $exception->getMessage(),
            ]);

            $this->processing = false;
            session()->flash('error', 'Unable to complete cash payment. Please try again.');
            return null;
        }

        session()->forget('active_order_id');
        $this->processing = false;

        return redirect()->route('payment.success', [
            'tenant' => $this->tenantId,
            'order' => $this->order,
        ]);
    }

    protected function startDigitalPayment()
    {
        $this->processing = true;

        if ($this->order->xendit_invoice_url) {
            $this->processing = false;
            return redirect()->away($this->order->xendit_invoice_url);
        }

        try {
            $invoice = $this->createInvoice($this->order);
        } catch (XenditSdkException $exception) {
            Log::error('Xendit invoice API error (Livewire payment page)', [
                'order_id' => $this->order->id,
                'message' => $exception->getMessage(),
                'payload' => $exception->getResponseBody() ?? [],
            ]);
            session()->flash('error', 'Unable to create digital invoice. Please try again.');
            $this->processing = false;
            return null;
        } catch (\Throwable $exception) {
            Log::error('Unexpected error while creating Xendit invoice (Livewire payment page)', [
                'order_id' => $this->order->id,
                'message' => $exception->getMessage(),
            ]);
            session()->flash('error', 'Payment service unavailable.');
            $this->processing = false;
            return null;
        }

        $this->order->forceFill([
            'xendit_invoice_id' => $invoice->getId(),
            'xendit_invoice_url' => $invoice->getInvoiceUrl(),
        ])->save();

        $this->processing = false;

        return redirect()->away($invoice->getInvoiceUrl());
    }

    protected function createInvoice(Order $order)
    {
        $secretKey = config('services.xendit.secret_key');

        if (! $secretKey) {
            throw new \RuntimeException('Missing Xendit secret key.');
        }

        Configuration::setXenditKey($secretKey);

        $invoiceApi = new InvoiceApi();
        $forUserId = config('services.xendit.platform_account_id');

        $invoiceRequest = new CreateInvoiceRequest([
            'external_id' => $order->reference_no,
            'amount' => (float) ($order->grand_total ?? $order->total),
            'description' => 'Payment for order ' . $order->reference_no,
            'success_redirect_url' => route('payment.success', ['tenant' => $this->tenantId, 'order' => $order]),
            'failure_redirect_url' => route('payment.failed', ['tenant' => $this->tenantId, 'order' => $order]),
            'payment_methods' => ['QRIS', 'CARD', 'EWALLET', 'BANK_TRANSFER'],
            'metadata' => [
                'order_id' => $order->id,
                'tenant_id' => $order->tenant_id,
                'callback_url' => route('payment.webhook'),
            ],
        ]);

        if ($order->customerDetail) {
            $invoiceRequest->setCustomer(new CustomerObject([
                'given_names' => $order->customerDetail->name,
                'email' => $order->customerDetail->email,
            ]));
        }

        $fee = config('services.xendit.application_fee', 0);

        if ($fee > 0) {
            $invoiceRequest->setFees([
                new InvoiceFee([
                    'type' => 'APPLICATION_FEE',
                    'value' => (float) $fee,
                ]),
            ]);
        }

        return $invoiceApi->createInvoice($invoiceRequest, $forUserId ?: null);
    }

    public function render(): View
    {
        return view('livewire.payment-page');
    }
}
