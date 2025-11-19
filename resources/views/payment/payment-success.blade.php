<x-customer.layout>
    @php
        $paymentChannel = $order->payment_channel ? strtoupper(str_replace('_', ' ', $order->payment_channel)) : 'PENDING';
        $totals = [
            'subtotal' => (float) ($order->subtotal ?? $order->total ?? 0),
            'total_tax' => (float) ($order->total_tax ?? 0),
            'grand_total' => (float) ($order->grand_total ?? $order->total ?? 0),
            'xendit_fee' => (float) ($order->xendit_fee ?? 0),
            'qash_fee' => (float) ($order->qash_fee ?? 0),
        ];
        $totals['fees_total'] = $totals['xendit_fee'] + $totals['qash_fee'];
        $totals['net'] = $totals['grand_total'] - $totals['fees_total'];
        $status = strtolower((string) ($order->status ?? 'confirmed'));
        $steps = [
            'confirmed' => in_array($status, ['confirmed', 'preparing', 'ready', 'completed'], true),
            'preparing' => in_array($status, ['preparing', 'ready', 'completed'], true),
            'ready' => in_array($status, ['ready', 'completed'], true),
        ];
    @endphp
    <div class="container py-5 text-black payment-feedback-wrapper">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-9 col-xl-8">
                <div class="bg-white shadow-lg border-0 rounded-5 p-4 p-md-5 payment-feedback-card">
                    <div class="text-center">
                        <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center mb-4"
                             style="width: 96px; height: 96px; background-color: rgba(25, 135, 84, 0.12);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" fill="currentColor"
                                 class="text-success" viewBox="0 0 16 16">
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.011-1.05"/>
                            </svg>
                        </div>
                        <h1 class="fw-bold mb-1">Payment Successful</h1>
                        <p class="text-muted mb-1">Reference <span class="fw-semibold">#{{ $order->reference_no }}</span></p>
                        <p class="text-muted">Paid via {{ $paymentChannel }}</p>
                        @if ($awaitingConfirmation)
                            <div class="alert alert-warning border-0 rounded-4 py-2 mt-3">
                                Awaiting payment confirmation...
                            </div>
                        @endif
                    </div>

                    <div class="row row-cols-1 row-cols-md-3 g-3 my-4 payment-feedback-stats">
                        <div class="col">
                            <div class="bg-light rounded-4 p-3 h-100">
                                <p class="text-muted text-uppercase small mb-1">Subtotal</p>
                                <p class="fw-bold fs-5 mb-0">{{ rupiah($totals['subtotal']) }}</p>
                            </div>
                        </div>
                        <div class="col">
                            <div class="bg-light rounded-4 p-3 h-100">
                                <p class="text-muted text-uppercase small mb-1">Total Tax</p>
                                <p class="fw-bold fs-5 mb-0">{{ rupiah($totals['total_tax']) }}</p>
                            </div>
                        </div>
                        <div class="col">
                            <div class="bg-light rounded-4 p-3 h-100">
                                <p class="text-muted text-uppercase small mb-1">Total (incl. tax)</p>
                                <p class="fw-bold fs-5 mb-0">{{ rupiah($totals['grand_total']) }}</p>
                            </div>
                        </div>
                    </div>

                    @include('payment.partials.order-items-table', ['order' => $order, 'totals' => $totals])

                    <div class="d-flex flex-column flex-md-row gap-3 mt-5 payment-feedback-actions">
                        <a href="{{ route('order.track', ['tenant' => request()->route('tenant'), 'order' => $order]) }}"
                           class="btn btn-dark btn-lg rounded-4 py-3 flex-fill">Track Order Status</a>
                        <a href="{{ route('cart.page', ['tenant' => request()->route('tenant')]) }}"
                           class="btn btn-outline-dark btn-lg rounded-4 py-3 flex-fill">Continue Browsing</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-customer.layout>
