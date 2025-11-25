<?php

namespace App\Services\Event;

use App\Models\Event;
use App\Models\Floor;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class EventService
{
    public function stats(?string $tenantId): array
    {
        $eventsQuery = Event::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId));

        return [
            'total' => (clone $eventsQuery)->count(),
            'upcoming' => (clone $eventsQuery)->whereDate('date', '>=', now()->toDateString())->count(),
            'featured' => (clone $eventsQuery)->where('is_featured', true)->count(),
        ];
    }

    public function formData(?Event $event, ?string $tenantId, ?string $tenantName, array $overrides = []): array
    {
        $locationOptions = $this->locationOptions($tenantId, $tenantName);
        $selectedLocation = $this->selectedLocationKey($event, $tenantName);

        if ($event && $selectedLocation && ! array_key_exists($selectedLocation, $locationOptions)) {
            $locationOptions = [$selectedLocation => $event->location] + $locationOptions;
        }

        $formDefaults = array_merge($this->buildFormDefaults($event), $overrides);

        return [
            'event' => $event,
            'eventTypes' => $this->eventTypeOptions(),
            'locationOptions' => $locationOptions,
            'selectedLocation' => $selectedLocation,
            'formDefaults' => $formDefaults,
        ];
    }

    public function prefillFromDiscount(Request $request): array
    {
        if (! $request->boolean('from_discount')) {
            return [];
        }

        $payload = ['from_discount' => true];

        if ($title = $request->query('title')) {
            $payload['title'] = $title;
        }

        if ($eventType = $this->normalizeEventType($request->query('event_type'))) {
            $payload['event_type'] = $eventType;
        }

        $dateFrom = $request->query('date_from');
        $dateTill = $request->query('date_till');

        if ($dateFrom && $dateTill) {
            try {
                $start = Carbon::parse($dateFrom)->startOfDay();
                $end = Carbon::parse($dateTill)->endOfDay();
                $payload['date_from'] = $start->format('Y-m-d\TH:i');
                $payload['date_till'] = $end->format('Y-m-d\TH:i');
                $payload['use_date_range'] = true;
            } catch (\Throwable $e) {
                // ignore invalid dates
            }
        }

        return $payload;
    }

    public function prepareAttributes(
        array $data,
        bool $useRange,
        bool $capacityUnlimited,
        bool $isFeatured,
        ?string $tenantId,
        ?string $tenantName
    ): array {
        [$location, $floorId] = $this->resolveLocationSelection($data['location'] ?? null, $tenantId, $tenantName);

        $data['location'] = $location;
        $data['floor_id'] = $floorId;
        $data['capacity'] = $capacityUnlimited ? null : ($data['capacity'] ?? null);
        if ($data['capacity'] !== null) {
            $data['capacity'] = (int) $data['capacity'];
        }
        $data['is_featured'] = $isFeatured;

        return $this->applyScheduleData($data, $useRange);
    }

    protected function locationOptions(?string $tenantId, ?string $tenantName): array
    {
        $options = ['main' => $this->mainLocationLabel($tenantName)];

        $floors = Floor::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        foreach ($floors as $floor) {
            $options['floor:' . $floor->id] = $floor->name;
        }

        return $options;
    }

    protected function selectedLocationKey(?Event $event, ?string $tenantName): ?string
    {
        if (! $event) {
            return null;
        }

        if ($event->floor_id) {
            return 'floor:' . $event->floor_id;
        }

        $mainLabel = $this->mainLocationLabel($tenantName);

        if ($event->location === $mainLabel) {
            return 'main';
        }

        return $event->location;
    }

    protected function resolveLocationSelection(?string $selection, ?string $tenantId, ?string $tenantName): array
    {
        if (! $selection) {
            return [null, null];
        }

        if ($selection === 'main') {
            return [$this->mainLocationLabel($tenantName), null];
        }

        if (Str::startsWith($selection, 'floor:')) {
            $floorId = (int) Str::after($selection, 'floor:');
            $floor = Floor::query()
                ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
                ->find($floorId);

            if ($floor) {
                return [$floor->name, $floor->id];
            }
        }

        return [$selection, null];
    }

    protected function eventTypeOptions(): array
    {
        return collect(Event::EVENT_TYPES)
            ->mapWithKeys(fn ($type) => [$type => Str::of($type)->replace('_', ' ')->title()])
            ->all();
    }

    protected function mainLocationLabel(?string $tenantName): string
    {
        return ($tenantName ? $tenantName . ' ' : '') . '(Main Location)';
    }

    protected function buildFormDefaults(?Event $event): array
    {
        $start = $event?->starts_at;
        $end = $event?->ends_at;

        return [
            'title' => $event?->title,
            'event_type' => $event?->event_type,
            'use_date_range' => $event?->uses_date_range ?? false,
            'event_date' => ($event && ! $event->uses_date_range && $start)
                ? $start->format('Y-m-d\TH:i')
                : null,
            'date_from' => ($event && $event->uses_date_range && $start)
                ? $start->format('Y-m-d\TH:i')
                : null,
            'date_till' => ($event && $event->uses_date_range && $end)
                ? $end->format('Y-m-d\TH:i')
                : null,
            'from_discount' => false,
        ];
    }

    protected function normalizeEventType(?string $eventType): ?string
    {
        if (! $eventType) {
            return null;
        }

        $normalized = Str::of($eventType)->lower()->snake()->value();

        if ($normalized === 'promo') {
            $normalized = 'promotions';
        }

        return in_array($normalized, Event::EVENT_TYPES, true) ? $normalized : null;
    }

    protected function applyScheduleData(array $data, bool $useRange): array
    {
        $data['uses_date_range'] = $useRange;

        if ($useRange) {
            $startInput = $data['date_from'] ?? null;
            $endInput = $data['date_till'] ?? null;

            $start = $startInput ? Carbon::parse($startInput) : null;
            $end = $endInput ? Carbon::parse($endInput) : null;

            $data['date_from'] = $start?->toDateTimeString();
            $data['date_till'] = $end?->toDateTimeString();
            $data['event_date'] = null;
            $data['date'] = $start?->toDateString();
            $data['time'] = $start?->format('H:i:s');
        } else {
            $eventDateInput = $data['event_date'] ?? null;
            $eventDate = $eventDateInput ? Carbon::parse($eventDateInput) : null;

            $data['event_date'] = $eventDate?->toDateTimeString();
            $data['date_from'] = null;
            $data['date_till'] = null;
            $data['date'] = $eventDate?->toDateString();
            $data['time'] = $eventDate?->format('H:i:s');
        }

        unset($data['use_date_range']);

        return $data;
    }
}
