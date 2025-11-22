<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
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
        'event_date',
        'date_from',
        'date_till',
        'uses_date_range',
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
        'event_date' => 'datetime',
        'date_from' => 'datetime',
        'date_till' => 'datetime',
        'uses_date_range' => 'boolean',
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
        $reference = $this->uses_date_range
            ? ($this->ends_at ?? $this->starts_at)
            : $this->starts_at;

        return $reference ? $reference->lt(now()) : false;
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

    public function getStartsAtAttribute(): ?Carbon
    {
        if ($this->uses_date_range && $this->date_from) {
            return $this->date_from instanceof Carbon ? $this->date_from : Carbon::parse($this->date_from);
        }

        if ($this->event_date) {
            return $this->event_date instanceof Carbon ? $this->event_date : Carbon::parse($this->event_date);
        }

        if ($this->date) {
            $timeString = null;

            if ($this->time instanceof Carbon) {
                $timeString = $this->time->format('H:i:s');
            } elseif (is_string($this->time)) {
                $timeString = $this->time;
            }

            $dateString = $this->date instanceof Carbon ? $this->date->format('Y-m-d') : $this->date;

            return Carbon::parse(trim($dateString . ' ' . ($timeString ?? '00:00:00')));
        }

        return null;
    }

    public function getEndsAtAttribute(): ?Carbon
    {
        if (! $this->uses_date_range) {
            return null;
        }

        if ($this->date_till) {
            return $this->date_till instanceof Carbon ? $this->date_till : Carbon::parse($this->date_till);
        }

        return $this->starts_at;
    }
}
