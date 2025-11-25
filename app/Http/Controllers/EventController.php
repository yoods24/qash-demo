<?php

namespace App\Http\Controllers;

use App\Http\Requests\Event\EventRequest;
use App\Models\Event;
use App\Models\Tenant;
use App\Services\Event\EventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function __construct(private EventService $events)
    {
    }

    public function index(): View
    {
        $tenantId = $this->tenantId();
        $stats = $this->events->stats($tenantId);

        return view('backoffice.events.index', [
            'stats' => $stats,
            'tenantId' => $tenantId,
        ]);
    }

    public function create(Request $request): View
    {
        $tenantId = $this->tenantId();
        $tenantName = $this->tenantName($tenantId);

        return view('backoffice.events.create', $this->events->formData(
            event: null,
            tenantId: $tenantId,
            tenantName: $tenantName,
            overrides: $this->events->prefillFromDiscount($request)
        ));
    }

    public function store(EventRequest $request): RedirectResponse
    {
        $tenantId = $this->tenantId();
        $tenantName = $this->tenantName($tenantId);

        $attributes = $this->events->prepareAttributes(
            data: $request->validated(),
            useRange: $request->boolean('use_date_range'),
            capacityUnlimited: $request->boolean('capacity_unlimited'),
            isFeatured: $request->boolean('is_featured'),
            tenantId: $tenantId,
            tenantName: $tenantName
        );

        $attributes['tenant_id'] = $tenantId;

        Event::create($attributes);

        return redirect()->route('backoffice.events.index')->with('message', 'Event created successfully.');
    }

    public function edit(Event $event): View
    {
        $this->ensureTenantOwnership($event);

        $tenantId = $this->tenantId();
        $tenantName = $this->tenantName($tenantId);

        return view('backoffice.events.edit', $this->events->formData($event, $tenantId, $tenantName));
    }

    public function update(EventRequest $request, Event $event): RedirectResponse
    {
        $this->ensureTenantOwnership($event);

        $tenantId = $this->tenantId();
        $tenantName = $this->tenantName($tenantId);

        $attributes = $this->events->prepareAttributes(
            data: $request->validated(),
            useRange: $request->boolean('use_date_range'),
            capacityUnlimited: $request->boolean('capacity_unlimited'),
            isFeatured: $request->boolean('is_featured'),
            tenantId: $tenantId,
            tenantName: $tenantName
        );

        $event->update($attributes);

        return redirect()->route('backoffice.events.index')->with('message', 'Event updated successfully.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->ensureTenantOwnership($event);

        $event->delete();

        return redirect()->route('backoffice.events.index')->with('message', 'Event deleted successfully.');
    }

    protected function tenantId(): string
    {
        $tenantId = tenant('id');

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

    protected function ensureTenantOwnership(Event $event): void
    {
        $tenantId = $this->tenantId();

        if ($tenantId && $event->tenant_id !== $tenantId) {
            abort(403);
        }
    }

}
