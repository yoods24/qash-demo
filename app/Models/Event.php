<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Event extends Model
{
    use HasFactory;
    use BelongsToTenant;

    public const EVENT_TYPES = [
        'entertainment',
        'announcement',
        'promotions',
        'special_event',
        'workshop',
        'community',
        'operational',
    ];

    protected $fillable = [
        'tenant_id',
        'floor_id',
        'title',
        'description',
        'event_type',
        'date',
        'time',
        'location',
        'about',
        'event_highlights',
        'what_to_expect',
        'capacity',
        'is_featured',
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'datetime:H:i',
        'is_featured' => 'boolean',
    ];

    protected $appends = [
        'is_expired',
        'about_points',
        'event_highlights_points',
        'what_to_expect_points',
    ];

    public function getIsExpiredAttribute(): bool
    {
        return $this->date < now()->toDateString();
    }

    public function getAboutPointsAttribute(): array
    {
        return $this->textToList($this->about);
    }

    public function getEventHighlightsPointsAttribute(): array
    {
        return $this->textToList($this->event_highlights);
    }

    public function getWhatToExpectPointsAttribute(): array
    {
        return $this->textToList($this->what_to_expect);
    }

    public function scopeForCurrentTenant($query)
    {
        $tenantId = function_exists('tenant') ? tenant('id') : null;

        if ($tenantId === null && auth()->check()) {
            $tenantId = auth()->user()->tenant_id;
        }

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        return $query;
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    protected function textToList(?string $text): array
    {
        if (! $text) {
            return [];
        }

        $normalized = str_replace(["\r\n", "\r"], "\n", $text);
        $normalized = str_replace(' - ', "\n", $normalized);
        $normalized = preg_replace('/(?:^|\n)\s*[-•]\s*/', "\n", $normalized) ?? $normalized;

        $lines = array_map('trim', explode("\n", $normalized));

        return array_values(array_filter($lines, fn ($line) => $line !== ''));
    }
}
