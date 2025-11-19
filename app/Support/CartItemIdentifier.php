<?php

namespace App\Support;

class CartItemIdentifier
{
    public static function make(int|string $productId, array $options = []): string
    {
        $hashSource = static::normalizeOptions($options);
        $hash = empty($hashSource) ? 'base' : substr(sha1(json_encode($hashSource)), 0, 16);

        return (string) $productId . '-' . $hash;
    }

    public static function extractProductId(object|array $item): ?int
    {
        $attributes = $item->attributes ?? null;

        if ($attributes instanceof \Illuminate\Support\Collection) {
            $candidate = $attributes->get('product_id');
            if ($candidate !== null) {
                return (int) $candidate;
            }
        } elseif (is_array($attributes) && array_key_exists('product_id', $attributes)) {
            return (int) $attributes['product_id'];
        }

        $rawId = $item->id ?? null;

        if (is_string($rawId) && str_contains($rawId, '-')) {
            $parts = explode('-', $rawId, 2);
            return is_numeric($parts[0]) ? (int) $parts[0] : null;
        }

        return is_numeric($rawId) ? (int) $rawId : null;
    }

    protected static function normalizeOptions(array $options): array
    {
        $normalized = [];

        foreach ($options as $key => $value) {
            $normalized[$key] = static::normalizeValue($value);
        }

        ksort($normalized);

        return $normalized;
    }

    protected static function normalizeValue(mixed $value): mixed
    {
        if (is_array($value)) {
            $child = [];
            foreach ($value as $childKey => $childValue) {
                $child[$childKey] = static::normalizeValue($childValue);
            }
            ksort($child);

            return $child;
        }

        return $value;
    }
}
