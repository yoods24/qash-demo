<x-backoffice.layout>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="fw-bold mb-0">Events</h2>
            <small class="text-muted">Coordinate upcoming experiences for your guests</small>
        </div>
        <div class="action-buttons d-flex gap-2">
            <a href="{{ route('backoffice.events.create') }}" class="btn btn-add">
                <i class="bi bi-calendar-plus me-1"></i> Add Event
            </a>
        </div>
    </div>

    @if (session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    <div class="order-summary mb-4">
        <div class="summary-container">
            <p class="data">{{ $stats['total'] }}</p>
            <p class="primer">Total Events</p>
        </div>
        <div class="summary-container">
            <p class="data">{{ $stats['upcoming'] }}</p>
            <p class="primer">Upcoming</p>
        </div>
        <div class="summary-container">
            <p class="data">{{ $stats['featured'] }}</p>
            <p class="primer">Featured</p>
        </div>
    </div>

    <div class="card p-3">
        @livewire('backoffice.tables.event-table', ['tenantParam' => $tenantId])
    </div>
</x-backoffice.layout>
