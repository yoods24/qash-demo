<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Floor;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        $tenantId = $this->tenantId();

        $eventsQuery = Event::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId));

        $stats = [
            'total' => (clone $eventsQuery)->count(),
            'upcoming' => (clone $eventsQuery)->whereDate('date', '>=', now()->toDateString())->count(),
            'featured' => (clone $eventsQuery)->where('is_featured', true)->count(),
        ];

        return view('backoffice.events.index', [
            'stats' => $stats,
            'tenantId' => $tenantId,
        ]);
    }

    public function create(): View
    {
        return view('backoffice.events.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = $this->tenantId();
        $tenantName = $this->tenantName($tenantId);

        $data = $this->validateEvent($request);
        [$location, $floorId] = $this->resolveLocationSelection($data['location'] ?? null, $tenantId, $tenantName);

        $data['location'] = $location;
        $data['floor_id'] = $floorId;
        $data['capacity'] = $request->boolean('capacity_unlimited') ? null : ($data['capacity'] ?? null);
        if ($data['capacity'] !== null) {
            $data['capacity'] = (int) $data['capacity'];
        }
        $data['is_featured'] = $request->boolean('is_featured');
        $data['tenant_id'] = $tenantId;

        Event::create($data);

        return redirect()->route('backoffice.events.index')->with('message', 'Event created successfully.');
    }

    public function edit(Event $event): View
    {
        $this->ensureTenantOwnership($event);

        return view('backoffice.events.edit', $this->formData($event));
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $this->ensureTenantOwnership($event);

        $tenantId = $this->tenantId();
        $tenantName = $this->tenantName($tenantId);

        $data = $this->validateEvent($request);
        [$location, $floorId] = $this->resolveLocationSelection($data['location'] ?? null, $tenantId, $tenantName);

        $data['location'] = $location;
        $data['floor_id'] = $floorId;
        $data['capacity'] = $request->boolean('capacity_unlimited') ? null : ($data['capacity'] ?? null);
        if ($data['capacity'] !== null) {
            $data['capacity'] = (int) $data['capacity'];
        }
        $data['is_featured'] = $request->boolean('is_featured');

        $event->update($data);

        return redirect()->route('backoffice.events.index')->with('message', 'Event updated successfully.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->ensureTenantOwnership($event);

        $event->delete();

        return redirect()->route('backoffice.events.index')->with('message', 'Event deleted successfully.');
    }

    protected function validateEvent(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'event_type' => ['required', Rule::in(Event::EVENT_TYPES)],
            'date' => ['required', 'date'],
            'time' => ['required', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:255'],
            'about' => ['nullable', 'string'],
            'event_highlights' => ['nullable', 'string'],
            'what_to_expect' => ['nullable', 'string'],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
        ]);
    }

    protected function formData(?Event $event = null): array
    {
        $tenantId = $this->tenantId();
        $tenantName = $this->tenantName($tenantId);

        $locationOptions = $this->locationOptions($tenantId, $tenantName);
        $selectedLocation = $this->selectedLocationKey($event, $tenantName);

        if ($event && $selectedLocation && ! array_key_exists($selectedLocation, $locationOptions)) {
            $locationOptions = [$selectedLocation => $event->location] + $locationOptions;
        }

        return [
            'event' => $event,
            'eventTypes' => $this->eventTypeOptions(),
            'locationOptions' => $locationOptions,
            'selectedLocation' => $selectedLocation,
        ];
    }

    protected function locationOptions(?string $tenantId, ?string $tenantName): array
    {
        $options = [];
        $options['main'] = $this->mainLocationLabel($tenantName);

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

    protected function tenantId(): string
    {
        $tenantId = auth()->user()->tenant_id ?? (function_exists('tenant') ? tenant('id') : null);

        if (! $tenantId) {
            abort(403, 'Tenant context is missing.');
        }

        return $tenantId;
    }

    protected function tenantName(?string $tenantId): ?string
    {
        if (function_exists('tenant') && tenant()) {
            return tenant()->data['name'] ?? tenant('id');
        }

        if ($tenantId) {
            return Tenant::where('id', $tenantId)->value('name') ?? $tenantId;
        }

        return null;
    }

    protected function mainLocationLabel(?string $tenantName): string
    {
        return ($tenantName ? $tenantName . ' ' : '') . '(Main Location)';
    }

    protected function ensureTenantOwnership(Event $event): void
    {
        $tenantId = $this->tenantId();

        if ($tenantId && $event->tenant_id !== $tenantId) {
            abort(403);
        }
    }
}
