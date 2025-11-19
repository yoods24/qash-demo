<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Models\Order;
use App\Services\OrderStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function __construct(private readonly OrderStockService $orderStockService)
    {
    }

    public function handle(Request $request)
    {
        $token = $request->header('x-callback-token');
        $expectedToken = config('services.xendit.callback_token');

        if (! $token || ! hash_equals((string) $expectedToken, (string) $token)) {
            Log::warning('Rejected Xendit webhook due to invalid callback token.', [
                'received' => $token,
            ]);

            return response()->json(['message' => 'Invalid callback token'], 401);
        }

        Log::info('Xendit webhook received', [
            'headers' => $request->headers->all(),
            'payload' => $request->all(),
        ]);

        $payload = $request->all();

        if (function_exists('ds')) {
            ds([
                'route' => 'payment.webhook.received',
                'headers' => $request->headers->all(),
                'payload' => $payload,
            ]);
        }

        $status = strtoupper((string) (data_get($payload, 'status') ?? data_get($payload, 'data.status') ?? ''));
        $externalId = data_get($payload, 'external_id', data_get($payload, 'data.external_id'));

        if (! $externalId) {
            return response()->json(['message' => 'Missing external_id'], 400);
        }

        $order = Order::withoutTenancy()->where('reference_no', $externalId)->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($order->payment_status === 'paid') {
            return response()->json(['message' => 'Already processed'], 200);
        }

        if ($status !== 'PAID') {
            return response()->json(['message' => 'Ignored'], 200);
        }

        try {
            DB::transaction(function () use ($order, $payload) {
                $order->refresh();
                $this->orderStockService->deduct($order);

                $order->forceFill([
                    'payment_status' => 'paid',
                    'payment_channel' => $this->mapPaymentChannel($payload),
                    'paid_at' => now(),
                    'status' => $order->status === 'waiting_for_payment' ? 'confirmed' : $order->status,
                    'xendit_invoice_id' => data_get($payload, 'id', $order->xendit_invoice_id),
                    'xendit_invoice_url' => data_get($payload, 'invoice_url', $order->xendit_invoice_url),
                ])->save();
            });
        } catch (InsufficientStockException $exception) {
            Log::error('Stock issue while processing Xendit webhook', [
                'order_id' => $order->id,
                'message' => $exception->getMessage(),
            ]);

            return response()->json(['message' => $exception->getMessage()], 409);
        } catch (\Throwable $exception) {
            Log::error('Failed to process Xendit webhook', [
                'order_id' => $order->id,
                'message' => $exception->getMessage(),
            ]);

            return response()->json(['message' => 'Failed to process webhook'], 500);
        }

        $order->refresh();

        if (function_exists('ds')) {
            ds([
                'route' => 'payment.webhook.processed',
                'order_id' => $order->id,
                'reference' => $order->reference_no,
                'payment_status' => $order->payment_status,
                'payment_channel' => $order->payment_channel,
            ]);
        }

        return response()->json(['message' => 'ok'], 200);
    }

    private function mapPaymentChannel(array $payload): ?string
    {
        $method = data_get($payload, 'payment_method')
            ?? data_get($payload, 'payment_channel')
            ?? data_get($payload, 'data.payment_method')
            ?? data_get($payload, 'data.payment_channel')
            ?? data_get($payload, 'payment_destination')
            ?? data_get($payload, 'data.payment_destination');

        return $method ? (string) $method : null;
    }
}
