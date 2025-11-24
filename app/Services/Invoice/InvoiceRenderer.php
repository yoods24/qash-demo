<?php

declare(strict_types=1);

namespace App\Services\Invoice;

use App\Models\Order;
use App\Models\Tenant;
use App\Models\TenantInvoiceSettings;
use App\Models\TenantProfile;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;
use App\Services\InvoiceSettingsService;

class InvoiceRenderer
{
    protected array $availableTemplates = ['template_1', 'template_2', 'template_3'];

    public function renderHtml(Order $order, TenantInvoiceSettings $settings, string $template): string
    {
        $view = $this->resolveTemplate($template);
        $order->loadMissing(['items', 'taxLines', 'customerDetail']);

        $tenantProfile = TenantProfile::firstOrNew(['tenant_id' => $order->tenant_id]);
        $tenant = tenant() ?: Tenant::find($order->tenant_id);
        $invoiceNumber = $this->buildInvoiceNumber($order, $settings);
        $dueDate = $this->resolveDueDate($order, $settings);
        $totals = $this->buildTotals($order, $settings);
        $orderTypeDisplay = app(InvoiceSettingsService::class)->formatOrderType($order);

        return View::make($view, [
            'order' => $order,
            'items' => $order->items,
            'taxLines' => $order->taxLines,
            'tenant' => $tenant,
            'tenantProfile' => $tenantProfile,
            'settings' => $settings,
            'invoiceNumber' => $invoiceNumber,
            'dueDate' => $dueDate,
            'customer' => $order->customerDetail,
            'totals' => $totals,
            'orderTypeDisplay' => $orderTypeDisplay,
        ])->render();
    }

    public function renderPdf(string $html): string
    {
        $pdf = Pdf::loadHTML($html)->setPaper('a4');

        return $pdf->output();
    }

    protected function resolveTemplate(string $template): string
    {
        if (! in_array($template, $this->availableTemplates, true)) {
            $template = 'template_1';
        }

        return "invoices.templates.{$template}";
    }

    protected function buildInvoiceNumber(Order $order, TenantInvoiceSettings $settings): string
    {
        $prefix = $settings->invoice_prefix ?? 'INV-';
        $sequence = str_pad((string) $order->id, 5, '0', STR_PAD_LEFT);

        return $prefix . $sequence;
    }

    protected function resolveDueDate(Order $order, TenantInvoiceSettings $settings): Carbon
    {
        $base = $order->created_at ?: now();
        $days = max(0, (int) $settings->invoice_due_days);

        return (clone $base)->addDays($days);
    }

    protected function buildTotals(Order $order, TenantInvoiceSettings $settings): array
    {
        $discount = (float) $order->items->sum(function ($item) {
            return (float) ($item->discount_amount ?? 0);
        });

        $grand = (float) $order->grand_total;
        $rounded = $this->applyRoundSetting($grand, $settings);

        return [
            'subtotal' => (float) $order->subtotal,
            'discount' => $discount,
            'tax' => (float) $order->total_tax,
            'grand_total' => $grand,
            'rounded_total' => $rounded,
        ];
    }

    protected function applyRoundSetting(float $amount, TenantInvoiceSettings $settings): ?float
    {
        if (! $settings->invoice_round_off) {
            return null;
        }

        return $settings->invoice_round_direction === 'down'
            ? floor($amount)
            : ceil($amount);
    }
}
