<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CustomerDetail;
use App\Models\DiningTable;
use Illuminate\Support\Facades\Session;

class OrderService
{
    public const ORDER_TYPE_SESSION_KEY = 'customer_order_type';
    public const DINE_IN = 'dine-in';
    public const TAKEAWAY = 'takeaway';

    public static function allowedTypes(): array
    {
        return [self::DINE_IN, self::TAKEAWAY];
    }

    public static function currentOrderType(): string
    {
        $hasTable = (bool) Session::get('dining_table_id');
        $type = Session::get(self::ORDER_TYPE_SESSION_KEY);

        $normalized = self::normalizeType($type, $hasTable);
        Session::put(self::ORDER_TYPE_SESSION_KEY, $normalized);

        return $normalized;
    }

    public static function persistOrderType(string $type): void
    {
        $type = in_array($type, self::allowedTypes(), true) ? $type : self::TAKEAWAY;
        Session::put(self::ORDER_TYPE_SESSION_KEY, $type);
    }

    public static function normalizeType(?string $type, bool $hasTable): string
    {
        $allowed = self::allowedTypes();
        $candidate = in_array($type, $allowed, true) ? $type : null;

        if ($candidate === self::DINE_IN && ! $hasTable) {
            return self::TAKEAWAY;
        }

        if ($candidate === null) {
            return $hasTable ? self::DINE_IN : self::TAKEAWAY;
        }

        return $candidate;
    }

    public static function clearTableAssignment(?int $customerId = null): void
    {
        $tenantId = function_exists('tenant') ? tenant('id') : null;
        $sessionTableId = Session::pull('dining_table_id');
        $tableId = $sessionTableId;

        if ($customerId) {
            $customer = CustomerDetail::find($customerId);
            if ($customer) {
                $tableId = $tableId ?: $customer->dining_table_id;
                if ($customer->dining_table_id !== null) {
                    $customer->update(['dining_table_id' => null]);
                }
            }
        }

        if ($tableId && $tenantId) {
            DiningTable::where('tenant_id', $tenantId)
                ->where('id', $tableId)
                ->update(['status' => 'available']);
        }
    }
}
