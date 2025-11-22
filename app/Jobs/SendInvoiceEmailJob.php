<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\InvoiceMail;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\TenantInvoiceSettings;
use App\Models\TenantInvoiceTemplate;
use App\Services\Invoice\InvoiceRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendInvoiceEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        public int $orderId,
        public string $tenantId
    ) {
    }

    public function handle(InvoiceRenderer $renderer): void
    {
        ds('SendInvoiceEmailJob:start', [
            'orderId' => $this->orderId,
            'tenantId' => $this->tenantId,
        ]);

        $tenant = Tenant::find($this->tenantId);
        if (! $tenant) {
            ds('SendInvoiceEmailJob:tenant_missing', ['tenantId' => $this->tenantId]);
            return;
        }

        tenancy()->initialize($tenant);

        try {
            $order = Order::with(['items', 'taxLines', 'customerDetail'])->find($this->orderId);
            if (! $order) {
                ds('SendInvoiceEmailJob:order_missing', ['orderId' => $this->orderId]);
                return;
            }

            $recipient = optional($order->customerDetail)->email;
            if (! $recipient) {
                ds('SendInvoiceEmailJob:no_recipient', ['orderId' => $order->id]);
                return;
            }

            $settings = TenantInvoiceSettings::firstOrCreate(
                ['tenant_id' => $order->tenant_id],
                [
                    'invoice_due_days' => 0,
                    'invoice_round_off' => false,
                    'invoice_round_direction' => 'up',
                    'show_company_details' => true,
                ]
            );

            $template = TenantInvoiceTemplate::firstOrCreate(
                ['tenant_id' => $order->tenant_id],
                ['selected_template' => 'template_1']
            );

            $html = $renderer->renderHtml($order, $settings, $template->selected_template);
            $pdf = $renderer->renderPdf($html);
            $invoiceNumber = ($settings->invoice_prefix ?? 'INV-') . str_pad((string) $order->id, 5, '0', STR_PAD_LEFT);

            ds('SendInvoiceEmailJob:ready_to_mail', [
                'orderId' => $order->id,
                'recipient' => $recipient,
                'template' => $template->selected_template,
                'invoiceNumber' => $invoiceNumber,
            ]);

            Mail::to($recipient)->send(new InvoiceMail(
                order: $order,
                pdfBinary: $pdf,
                invoiceNumber: $invoiceNumber,
                tenantName: $tenant->name
            ));

            ds('SendInvoiceEmailJob:mail_sent', ['orderId' => $order->id, 'recipient' => $recipient]);
        } finally {
            tenancy()->end();
        }
    }
}
