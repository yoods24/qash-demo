<?php

namespace App\Services\Reports;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SalesChartService
{
    /**
     * Build a net sales time series grouped by day or month.
     *
     * @return array{labels: array<int, string>, data: array<int, float>}
     */
public function getNetSalesSeries(Carbon $startDate, Carbon $endDate, string $granularity = 'daily'): array
{
    $ordersQuery = $this->baseOrdersQuery($startDate, $endDate);

    // Build a pure SQL expression string for the period
    $dateExpression = $granularity === 'monthly'
        ? "DATE_FORMAT(o.created_at, '%Y-%m-01')"  // first day of month
        : "DATE(o.created_at)";                    // just the date

    // Eloquent builder → base query for fromSub
    $netSub = $this->ordersWithNetSubquery($ordersQuery)->toBase();

    $rows = DB::query()
        ->fromSub($netSub, 'o')
        ->selectRaw("$dateExpression as period")
        ->selectRaw('COALESCE(SUM(net_per_order), 0) as total_net')
        ->groupBy('period')
        ->orderBy('period')
        ->get();

    $labels = [];
    $data = [];

    foreach ($rows as $row) {
        $periodDate = Carbon::parse($row->period);

        $labels[] = $granularity === 'monthly'
            ? $periodDate->format('M Y')
            : $periodDate->toDateString();

        $data[] = (float) $row->total_net;
    }

    return [
        'labels' => $labels,
        'data' => $data,
    ];
}


    private function baseOrdersQuery(Carbon $startDate, Carbon $endDate): EloquentBuilder
    {
        return Order::query()
            ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->whereIn('status', $this->successfulStatuses())
            ->where('payment_status', 'paid')
            ->when(function_exists('tenant') ? tenant('id') : null, function (EloquentBuilder $query, $tenantId) {
                $query->where('tenant_id', $tenantId);
            });
    }

    private function ordersWithNetSubquery(EloquentBuilder $ordersQuery): EloquentBuilder
    {
        $base = clone $ordersQuery;

        return $base
            ->select([
                'orders.id',
                'orders.created_at',
                DB::raw('COALESCE(order_item_totals.total_sales, 0) as total_sales'),
                DB::raw('COALESCE(order_item_totals.total_discount, 0) as total_discount'),
                DB::raw('(COALESCE(order_item_totals.total_sales, 0) - COALESCE(order_item_totals.total_discount, 0)) as net_per_order'),
            ])
            ->leftJoinSub($this->orderItemTotalsSub(), 'order_item_totals', function ($join) {
                $join->on('orders.id', '=', 'order_item_totals.order_id');
            });
    }

    private function orderItemTotalsSub(): QueryBuilder
    {
        return DB::table('order_items')
            ->select('order_id')
            ->selectRaw('SUM(quantity * unit_price) as total_sales')
            ->selectRaw('SUM(discount_amount) as total_discount')
            ->groupBy('order_id');
    }

    private function successfulStatuses(): array
    {
        return ['confirmed', 'preparing', 'ready'];
    }
}
