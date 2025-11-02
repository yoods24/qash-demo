<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsController extends Controller
{
    public function index()
    {
        return view('backoffice.reports.index');
    }

    public function sales(Request $request)
    {
        $tenantId = function_exists('tenant') ? tenant('id') : null;
        $from = $request->input('from');
        $to = $request->input('to');

        // Defaults: last 7 days
        if (!$from || !$to) {
            $from = now()->subDays(6)->toDateString();
            $to = now()->toDateString();
        }

        $start = \Carbon\Carbon::parse($from)->startOfDay();
        $end = \Carbon\Carbon::parse($to)->endOfDay();

        $base = DB::table('orders')
            ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
            ->selectRaw('DATE(orders.created_at) as day')
            ->selectRaw('COUNT(DISTINCT orders.id) as total_orders')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as total_products')
            ->selectRaw('COALESCE(SUM(order_items.unit_price * order_items.quantity), 0) as subtotal')
            ->selectRaw('COALESCE(SUM(orders.total), 0) as total')
            ->whereBetween('orders.created_at', [$start, $end])
            ->groupBy('day')
            ->orderBy('day', 'desc');

        if ($tenantId !== null) {
            $base->where('orders.tenant_id', $tenantId);
        }

        // Optional filters
        if ($status = $request->input('status')) {
            $base->where('orders.status', $status);
        }
        if ($payment = $request->input('payment_status')) {
            $base->where('orders.payment_status', $payment);
        }

        $rows = $base->get()->map(function ($r) {
            $r->tax = max(0, (float) $r->total - (float) $r->subtotal);
            return $r;
        });

        if ($request->boolean('export')) {
            $filename = 'sales_report_' . now()->format('Ymd_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            return new StreamedResponse(function () use ($rows) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Date', 'Total Orders', 'Total Products', 'Subtotal', 'Tax', 'Total']);
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->day,
                        (int) $r->total_orders,
                        (int) $r->total_products,
                        number_format((float) $r->subtotal, 2, '.', ''),
                        number_format((float) $r->tax, 2, '.', ''),
                        number_format((float) $r->total, 2, '.', ''),
                    ]);
                }
                fclose($out);
            }, 200, $headers);
        }

        return view('backoffice.reports.sales', [
            'rows' => $rows,
            'from' => $from,
            'to' => $to,
            'status' => $request->input('status'),
            'payment_status' => $request->input('payment_status'),
        ]);
    }

    public function productsPurchase(Request $request)
    {
        $tenantId = function_exists('tenant') ? tenant('id') : null;
        $from = $request->input('from');
        $to = $request->input('to');

        if (!$from || !$to) {
            $from = now()->subDays(6)->toDateString();
            $to = now()->toDateString();
        }

        $start = \Carbon\Carbon::parse($from)->startOfDay();
        $end = \Carbon\Carbon::parse($to)->endOfDay();

        $q = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->selectRaw('DATE(orders.created_at) as day')
            ->selectRaw('COALESCE(order_items.product_id, 0) as product_id')
            ->selectRaw('order_items.product_name as product_name')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as quantity')
            ->selectRaw('COALESCE(SUM(order_items.quantity * order_items.unit_price), 0) as total')
            ->whereBetween('orders.created_at', [$start, $end])
            ->groupBy('day', 'product_id', 'product_name')
            ->orderBy('day', 'desc')
            ->orderBy('product_name');

        if ($tenantId !== null) {
            $q->where('orders.tenant_id', $tenantId);
            $q->where('order_items.tenant_id', $tenantId);
        }

        if ($status = $request->input('status')) {
            $q->where('orders.status', $status);
        }
        if ($payment = $request->input('payment_status')) {
            $q->where('orders.payment_status', $payment);
        }

        $rows = $q->get();

        if ($request->boolean('export')) {
            $filename = 'products_purchase_' . now()->format('Ymd_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];
            return new StreamedResponse(function () use ($rows) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Date', 'Product', 'Quantity', 'Total']);
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->day,
                        $r->product_name,
                        (int) $r->quantity,
                        number_format((float) $r->total, 2, '.', ''),
                    ]);
                }
                fclose($out);
            }, 200, $headers);
        }

        return view('backoffice.reports.products-purchase', [
            'rows' => $rows,
            'from' => $from,
            'to' => $to,
            'status' => $request->input('status'),
            'payment_status' => $request->input('payment_status'),
        ]);
    }
}
