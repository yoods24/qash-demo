<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Http\Requests\Backoffice\SalesReportFilterRequest;
use App\Services\Reports\SalesReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Carbon;

class ReportsController extends Controller
{
    public function index()
    {
        return view('backoffice.reports.index');
    }

    public function sales(SalesReportFilterRequest $request, SalesReportService $service)
    {
        $startDate = $request->startDate();
        $endDate = $request->endDate();
        $paymentStatus = $request->paymentStatus();

        $result = $service->getSummaryAndOrders($startDate, $endDate, $request->input('status'), $paymentStatus);

        return view('backoffice.reports.sales', [
            'orders' => $result['orders'],
            'totalQuantity' => $result['totalQuantity'],
            'totalSalesBeforeDiscount' => $result['totalSalesBeforeDiscount'],
            'totalDiscount' => $result['totalDiscount'],
            'netSales' => $result['netSales'],
            'totalTax' => $result['totalTax'],
            'totalCogs' => $result['totalCogs'],
            'grossProfit' => $result['grossProfit'],
            'startDate' => $startDate,
            'endDate' => $endDate,
            'granularity' => $request->granularity(),
            'status' => $request->input('status'),
            'paymentStatus' => $paymentStatus,
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

    public function kitchenPerformance(Request $request)
    {
        $tenantId = function_exists('tenant') ? tenant('id') : null;
        $from = $request->input('from');
        $to = $request->input('to');
        if (!$from || !$to) {
            $from = now()->startOfMonth()->toDateString();
            $to = now()->toDateString();
        }
        $start = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->endOfDay();

        $orders = Order::query()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'ready')
            ->whereBetween('ready_at', [$start, $end])
            ->get();

        $totalCompleted = $orders->count();
        $onTime = 0; $lateWarn = 0; $lateDanger = 0;
        foreach ($orders as $o) {
            $expected = (int) ($o->expected_seconds_total ?? 0);
            $total = (int) ($o->total_seconds ?? 0);
            if ($total <= 0 && $o->confirmed_at && $o->ready_at) {
                $total = max(0, $o->ready_at->diffInSeconds($o->confirmed_at));
            }
            if ($total > ($expected + 600)) $lateDanger++;
            elseif ($total > ($expected + 300)) $lateWarn++;
            else $onTime++;
        }

        // Daily ratings -> monthly average (0..5)
        $byDay = $orders->groupBy(fn($o) => optional($o->ready_at)->toDateString());
        $dailyRatings = [];
        foreach ($byDay as $day => $group) {
            $tc = $group->count();
            $ot = 0; $lw = 0; $ld = 0;
            foreach ($group as $o) {
                $expected = (int) ($o->expected_seconds_total ?? 0);
                $total = (int) ($o->total_seconds ?? 0);
                if ($total <= 0 && $o->confirmed_at && $o->ready_at) {
                    $total = max(0, $o->ready_at->diffInSeconds($o->confirmed_at));
                }
                if ($total > ($expected + 600)) $ld++;
                elseif ($total > ($expected + 300)) $lw++;
                else $ot++;
            }
            $score = $tc > 0 ? 5 * (($ot + 0.5 * $lw) / $tc) : 0; // half credit for warning
            $dailyRatings[] = ['day' => $day, 'score' => $score];
        }
        $monthlyRating = count($dailyRatings) ? round(collect($dailyRatings)->avg('score'), 2) : 0;

        return view('backoffice.reports.kitchen-performance', [
            'from' => $from,
            'to' => $to,
            'summary' => compact('totalCompleted','onTime','lateWarn','lateDanger','monthlyRating'),
            'dailyRatings' => $dailyRatings,
        ]);
    }
}
