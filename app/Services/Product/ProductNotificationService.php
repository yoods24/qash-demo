<?php

namespace App\Services\Product;

use Throwable;
use App\Models\Product;
use App\Models\TenantNotification;
use Illuminate\Support\Facades\Log;
use App\Services\User\UserNameResolver;
use Illuminate\Contracts\Auth\Authenticatable;

class ProductNotificationService
{
    public function created(Product $product, ?Authenticatable $user = null): void
    {
        $tenantId = function_exists('tenant') ? tenant('id') : null;
        if (! $tenantId) {
            return;
        }

        $message = "Product '{$product->name}' has been created";
        $name = UserNameResolver::resolve($user);


        if ($name !== null) {
            $message .= " by {$name}";
        }
        $message .= '.';

        $this->sendNotification(
            tenantId: $tenantId,
            title: 'New Product Created',
            description: $message,
            product: $product,
        );
    }

    private function sendNotification(string $tenantId, string $title, string $description, Product $product): void
    {
        try {
            TenantNotification::create([
                'tenant_id' => $tenantId,
                'type' => 'product',
                'title' => $title,
                'description' => $description,
                'item_id' => $product->id,
                'route_name' => 'backoffice.product.edit',
                'route_params' => [
                    'product' => $product->id,
                    'tenant' => $tenantId,
                ],
            ]);
        } catch (Throwable $e) {
            Log::warning('Failed to create product notification', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
