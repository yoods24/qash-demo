<x-customer.layout>
    @php
        $paymentChannel = $order->payment_channel ? strtoupper(str_replace('_', ' ', $order->payment_channel)) : 'UNPAID';
        $totals = [
            'subtotal' => (float) ($order->subtotal ?? $order->total ?? 0),
            'total_tax' => (float) ($order->total_tax ?? 0),
            'grand_total' => (float) ($order->grand_total ?? $order->total ?? 0),
            'xendit_fee' => (float) ($order->xendit_fee ?? 0),
            'qash_fee' => (float) ($order->qash_fee ?? 0),
        ];
        $totals['fees_total'] = $totals['xendit_fee'] + $totals['qash_fee'];
        $totals['net'] = $totals['grand_total'] - $totals['fees_total'];
        $status = strtolower((string) ($order->status ?? 'pending'));
        $steps = [
            'confirmed' => in_array($status, ['confirmed', 'preparing', 'ready'], true),
            'preparing' => in_array($status, ['preparing', 'ready'], true),
            'ready' => $status === 'ready',
        ];
    @endphp

    <livewire:customer.order-tracker-header :order="$order" />

    <div class="container py-5 text-black payment-feedback-wrapper">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-9 col-xl-8">
                <div class="bg-white shadow-lg border-0 rounded-5 p-4 p-md-5 payment-feedback-card">
                    <div class="text-center">
                        <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center mb-4"
                             style="width: 96px; height: 96px; background-color: rgba(220, 53, 69, 0.12);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" fill="currentColor"
                                 class="text-danger" viewBox="0 0 16 16">
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-5.146-2.854a.5.5 0 0 0-.708 0L8 7.293 5.854 5.146a.5.5 0 1 0-.708.708L7.293 8l-2.147 2.146a.5.5 0 0 0 .708.708L8 8.707l2.146 2.147a.5.5 0 0 0 .708-.708L8.707 8l2.147-2.146a.5.5 0 0 0 0-.708"/>
                            </svg>
                        </div>
                        <h1 class="fw-bold mb-1 text-danger">Payment Failed</h1>
                        <p class="text-muted mb-1">Reference <span class="fw-semibold">#{{ $order->reference_no }}</span></p>
                        <p class="text-muted">Attempted via {{ $paymentChannel }}</p>
                        <div class="alert alert-danger border-0 rounded-4 py-2 mt-3">
                            We were unable to confirm your payment. Please try another payment method.
                        </div>
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

                    <div class="d-flex justify-content-between gap-3 mb-4 flex-column flex-md-row">
                        @php
                            $labels = ['confirmed' => 'Confirmed', 'preparing' => 'Preparing', 'ready' => 'Ready'];
                            $index = 1;
                        @endphp
                        @foreach ($labels as $key => $label)
                            @php $isActive = $steps[$key] ?? false; @endphp
                            <div class="flex-fill text-center">
                                <div class="rounded-circle mx-auto mb-2 border fw-bold {{ $isActive ? 'bg-success bg-opacity-10 text-success border-success' : 'bg-light text-secondary border-0' }}"
                                     style="width: 64px; height: 64px; line-height: 64px;">
                                    {{ $index }}
                                </div>
                                <p class="mb-0 fw-semibold text-capitalize">{{ $label }}</p>
                            </div>
                            @php $index++; @endphp
                        @endforeach
                    </div>

                    @include('payment.partials.order-items-table', ['order' => $order, 'totals' => $totals])

                    <div class="d-flex flex-column flex-md-row gap-3 mt-5 payment-feedback-actions">
                        <a href="{{ route('payment.show', ['tenant' => request()->route('tenant'), 'order' => $order]) }}"
                           class="btn btn-dark btn-lg rounded-4 py-3 flex-fill">Choose Payment Method</a>
                        <a href="{{ route('cart.page', ['tenant' => request()->route('tenant')]) }}"
                           class="btn btn-outline-dark btn-lg rounded-4 py-3 flex-fill">Back to Cart</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-customer.layout>
