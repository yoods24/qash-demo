<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        protected string $pdfBinary,
        protected string $invoiceNumber,
        protected ?string $tenantName = null
    ) {
    }

    public function build(): self
    {
        $tenantName = $this->tenantName ?: 'Qash Tenant';

        return $this
            ->subject('Your Receipt from ' . $tenantName)
            ->view('emails.invoices.default')
            ->with([
                'order' => $this->order,
                'tenantName' => $tenantName,
                'invoiceNumber' => $this->invoiceNumber,
            ])
            ->attachData(
                $this->pdfBinary,
                $this->invoiceNumber . '.pdf',
                ['mime' => 'application/pdf']
            );
    }
}
