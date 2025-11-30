<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Models\Order;
use App\Services\OrderStockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Xendit\Configuration;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Invoice\CustomerObject;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice\InvoiceFee;
use Xendit\XenditSdkException;
use App\Support\OrderItemOptionHydrator;

class PaymentController extends Controller
{
    public function __construct(private readonly OrderStockService $orderStockService)
    {
    }

    public function show(Request $request, Order $order): View|RedirectResponse
    {
        $this->assertTenantOrder($request, $order);

        if ($order->payment_status === 'paid') {
            return redirect()->route('payment.success', [
                'tenant' => $request->route('tenant'),
                'order' => $order,
            ]);
        }

        $order = OrderItemOptionHydrator::hydrate($order);

        return view('payment.payment-page', [
            'order' => $order,
        ]);
    }

    public function process(Request $request, Order $order): RedirectResponse
    {
        $this->assertTenantOrder($request, $order);

        if ($order->payment_status === 'paid') {
            return redirect()->route('payment.success', [
                'tenant' => $request->route('tenant'),
                'order' => $order,
            ]);
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:cash,digital',
        ]);

        return $validated['payment_method'] === 'cash'
            ? $this->handleCash($request, $order)
            : $this->handleDigital($request, $order);
    }

    public function success(Request $request, Order $order): View
    {
        $this->assertTenantOrder($request, $order);

        if (function_exists('ds')) {
            ds([
                'route' => 'payment.success',
                'order_id' => $order->id,
                'reference' => $order->reference_no,
                'query' => $request->all(),
            ]);
        }

        $order = OrderItemOptionHydrator::hydrate($order);

        return view('payment.payment-success', [
            'order' => $order,
            'awaitingConfirmation' => $order->payment_status !== 'paid',
        ]);
    }

    public function failed(Request $request, Order $order): View
    {
        $this->assertTenantOrder($request, $order);

        $order = OrderItemOptionHydrator::hydrate($order);

        return view('payment.payment-failed', [
            'order' => $order,
        ]);
    }

    private function handleCash(Request $request, Order $order): RedirectResponse
    {
        try {
            DB::transaction(function () use (&$order) {
                $order = $order->fresh();
                $this->orderStockService->deduct($order);
                $order->forceFill([
                    'payment_status' => 'paid',
                    'payment_channel' => 'cash',
                    'status' => 'confirmed',
                    'paid_at' => now(),
                ])->save();
            });
        } catch (InsufficientStockException $exception) {
            return back()->withErrors(['payment_method' => $exception->getMessage()]);
        } catch (\Throwable $exception) {
            Log::error('Failed to record cash payment', [
                'order_id' => $order->id,
                'exception' => $exception->getMessage(),
            ]);

            return back()->withErrors(['payment_method' => 'Unable to complete cash payment. Please try again.']);
        }

        session()->forget('active_order_id');

        return redirect()->route('payment.success', [
            'tenant' => $request->route('tenant'),
            'order' => $order,
        ]);
    }

    private function handleDigital(Request $request, Order $order): RedirectResponse
    {
        $order->loadMissing('customerDetail');

        if ($order->xendit_invoice_url) {
            return redirect()->away($order->xendit_invoice_url);
        }

        try {
            $invoice = $this->createInvoice($request, $order);
        } catch (XenditSdkException $exception) {
            Log::error('Xendit invoice API error', [
                'order_id' => $order->id,
                'message' => $exception->getMessage(),
                'payload' => $exception->getResponseBody() ?? [],
            ]);

            return back()->withErrors(['payment_method' => 'Unable to create digital invoice. Please try again.']);
        } catch (\Throwable $exception) {
            Log::error('Unexpected error while creating Xendit invoice', [
                'order_id' => $order->id,
                'message' => $exception->getMessage(),
            ]);

            return back()->withErrors(['payment_method' => 'Payment service unavailable.']);
        }

        $order->forceFill([
            'xendit_invoice_id' => $invoice->getId(),
            'xendit_invoice_url' => $invoice->getInvoiceUrl(),
        ])->save();

        if (function_exists('ds')) {
            ds([
                'context' => 'payment-controller.handle-digital',
                'order_id' => $order->id,
                'invoice_payload' => method_exists($invoice, 'jsonSerialize') ? $invoice->jsonSerialize() : $invoice,
            ]);
        }

        return redirect()->away($invoice->getInvoiceUrl());
    }

    private function createInvoice(Request $request, Order $order)
    {
        $secretKey = config('services.xendit.secret_key');

        if (! $secretKey) {
            throw new \RuntimeException('Missing Xendit secret key.');
        }

        Configuration::setXenditKey($secretKey);

        $invoiceApi = new InvoiceApi();
        $forUserId = config('services.xendit.platform_account_id');

        $customerDetail = $order->customerDetail;

        $invoiceRequest = new CreateInvoiceRequest([
            'external_id' => $order->reference_no,
            'amount' => (float) ($order->grand_total ?? $order->total),
            'description' => 'Payment for order ' . $order->reference_no,
            'success_redirect_url' => route('payment.success', ['tenant' => $request->route('tenant'), 'order' => $order]),
            'failure_redirect_url' => route('payment.failed', ['tenant' => $request->route('tenant'), 'order' => $order]),
            'payment_methods' => ['QRIS', 'CARD', 'EWALLET', 'BANK_TRANSFER'],
            'metadata' => [
                'order_id' => $order->id,
                'tenant_id' => $order->tenant_id,
                'callback_url' => route('payment.webhook'),
            ],
        ]);

        if ($customerDetail) {
            $invoiceRequest->setCustomer(new CustomerObject([
                'given_names' => $customerDetail->name,
                'email' => $customerDetail->email,
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

        return $invoiceApi->createInvoice(
            $invoiceRequest,
            $forUserId ?: null
        );
    }

    private function assertTenantOrder(Request $request, Order $order): void
    {
        $tenantId = (string) ($request->route('tenant') ?? tenant('id'));

        if ($order->tenant_id !== $tenantId) {
            abort(404);
        }
    }
}
