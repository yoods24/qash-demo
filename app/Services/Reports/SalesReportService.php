<?php

namespace App\Services\Reports;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class SalesReportService
{
    /**
     * Return summary metrics and paginated orders for the given period.
     *
     * @return array{
     *     orders: LengthAwarePaginator,
     *     totalQuantity: float|int,
     *     totalSalesBeforeDiscount: float,
     *     totalDiscount: float,
     *     netSales: float,
     *     totalTax: float,
     *     totalCogs: float,
     *     grossProfit: float
     * }
     */
    public function getSummaryAndOrders(Carbon $startDate, Carbon $endDate, ?string $statusFilter = null, ?string $paymentStatus = 'paid'): array
    {
        $ordersQuery = $this->baseOrdersQuery($startDate, $endDate, $statusFilter, $paymentStatus);

        $orders = (clone $ordersQuery)
            ->with('customerDetail')
            ->latest('created_at')
            ->paginate(15);

        $totalQuantity = $this->sumTotalQuantity($startDate, $endDate, $statusFilter, $paymentStatus);
        $salesTotals = $this->sumSalesAndDiscount($startDate, $endDate, $statusFilter, $paymentStatus);
        $totalTax = (clone $ordersQuery)->sum('total_tax');
        $totalCogs = $this->sumTotalCogs($startDate, $endDate, $statusFilter, $paymentStatus);
        $netSales = max(0, $salesTotals['totalSalesBeforeDiscount'] - $salesTotals['totalDiscount']);
        $grossProfit = $netSales - $totalCogs;

        return [
            'orders' => $orders,
            'totalQuantity' => $totalQuantity,
            'totalSalesBeforeDiscount' => $salesTotals['totalSalesBeforeDiscount'],
            'totalDiscount' => $salesTotals['totalDiscount'],
            'netSales' => $netSales,
            'totalTax' => $totalTax,
            'totalCogs' => $totalCogs,
            'grossProfit' => $grossProfit,
        ];
    }

    private function baseOrdersQuery(Carbon $startDate, Carbon $endDate, ?string $statusFilter = null, ?string $paymentStatus = 'paid'): Builder
    {
        return Order::query()
            ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->whereIn('status', $this->successfulStatuses())
            ->when($paymentStatus, fn (Builder $query) => $query->where('payment_status', $paymentStatus))
            ->when(function_exists('tenant') ? tenant('id') : null, function (Builder $query, $tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->when($statusFilter, function (Builder $query, string $statusFilter) {
                $query->where('status', $statusFilter);
            });
    }

    private function sumTotalQuantity(Carbon $startDate, Carbon $endDate, ?string $statusFilter = null, ?string $paymentStatus = 'paid'): float|int
    {
        return OrderItem::query()
            ->whereIn('order_id', $this->orderIdsSubQuery($startDate, $endDate, $statusFilter, $paymentStatus))
            ->sum('quantity');
    }

    private function sumSalesAndDiscount(Carbon $startDate, Carbon $endDate, ?string $statusFilter = null, ?string $paymentStatus = 'paid'): array
    {
        $row = OrderItem::query()
            ->whereIn('order_id', $this->orderIdsSubQuery($startDate, $endDate, $statusFilter, $paymentStatus))
            ->selectRaw('COALESCE(SUM(quantity * unit_price), 0) as sales')
            ->selectRaw('COALESCE(SUM(discount_amount), 0) as discount')
            ->first();

        return [
            'totalSalesBeforeDiscount' => (float) ($row?->sales ?? 0),
            'totalDiscount' => (float) ($row?->discount ?? 0),
        ];
    }

    private function sumTotalCogs(Carbon $startDate, Carbon $endDate, ?string $statusFilter = null, ?string $paymentStatus = 'paid'): float
    {
        $row = OrderItem::query()
            ->whereIn('order_id', $this->orderIdsSubQuery($startDate, $endDate, $statusFilter, $paymentStatus))
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->selectRaw('COALESCE(SUM(order_items.quantity * COALESCE(products.goods_price, 0)), 0) as cogs')
            ->first();

        return (float) ($row?->cogs ?? 0);
    }

    private function orderIdsSubQuery(Carbon $startDate, Carbon $endDate, ?string $statusFilter = null, ?string $paymentStatus = 'paid'): Builder
    {
        return $this->baseOrdersQuery($startDate, $endDate, $statusFilter, $paymentStatus)->select('id');
    }

    private function successfulStatuses(): array
    {
        return ['confirmed', 'preparing', 'ready'];
    }
}
