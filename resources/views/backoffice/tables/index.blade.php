<x-backoffice.layout>
    <div class="d-flex flex-wrap justify-content-between align-items-end mb-3 gap-2">
        <div>
            <h4 class="mb-1">Dining Tables</h4>
            <div class="text-muted small">Arrange and manage tables for tenant {{ tenant('id') }}</div>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-12">
            <div class="card card-white p-3">
                <div class="fw-semibold mb-2">Selected Table</div>
                <form id="editTableForm" class="row g-2 align-items-end" autocomplete="off">
                    <input type="hidden" name="id" id="tableId" />
                    <div class="col">
                        <label class="form-label">Label</label>
                        <input type="text" class="form-control w-100" id="tableLabel" name="label" placeholder="Table A" />
                    </div>
                    <div class="col">
                        <label class="form-label">Status</label>
                        <select class="form-select w-100" id="tableStatus" name="status">
                            <option value="available">Available</option>
                            <option value="occupied">Occupied</option>
                            <option value="oncleaning">On Cleaning</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                    <div class="col">
                        <label class="form-label">Shape</label>
                        <select class="form-select w-100" id="tableShape" name="shape">
                            <option value="rectangle">Rectangle</option>
                            <option value="circle">Circle</option>
                        </select>
                    </div>
                    <div class="col">
                        <label class="form-label">Capacity</label>
                        <input type="number" class="form-control w-100" id="tableCapacity" name="capacity" min="1" max="50" />
                    </div>
                    <div class="col">
                        <label class="form-label">Color</label>
                        <input type="color" class="form-control form-control-color w-50" id="tableColor" name="color" value="#f1f5f9" />
                    </div>
                <div class="small text-muted mt-4 justify-content-between d-flex text-center align-items-center">
                    Tip: Click a table tile or the pencil icon to edit.
                    <div>
                        <button type="button" id="deleteTableBtn" class="btn btn-outline-danger btn-sm" disabled>Delete</button>
                        <button type="submit" class="btn btn-primer btn-sm" disabled>Save</button>
                    </div>
                </div>
                </form>
            </div>
        </div>
        <div class="col-12">
            <div class="ms-auto d-flex gap-2 p-3 justify-content-between">
                <div class="mb-3">
                    @php $selected = $currentFloorId ?? null; @endphp
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        @foreach($floors as $f)
                            <a href="{{ route('backoffice.tables.index', ['tenant' => tenant('id'), 'floor' => $f->id]) }}"
                            class="btn btn-sm {{ $selected === $f->id ? 'btn-primary' : 'btn-outline-primary' }}">
                                {{ $f->name }}
                                @if($f->area_type)
                                    <span class="badge bg-light text-dark ms-1">{{ $f->area_type }}</span>
                                @endif
                            </a>
                        @endforeach
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addFloorModal">
                            <i class="bi bi-plus-lg me-1"></i> Add Floor
                        </button>

                        @if(($floors->count() ?? 0) > 0)
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-danger dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Manage Selected
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <form action="{{ route('backoffice.floors.update', ['tenant' => tenant('id'), 'floor' => $selected]) }}" method="POST" class="px-3 py-2 d-grid gap-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="name" class="form-control form-control-sm" placeholder="Rename floor" required>
                                        <input type="text" name="area_type" class="form-control form-control-sm" placeholder="Area type">
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                                    </form>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                @if(($floors->count() ?? 0) > 1)
                                    <li>
                                        <form action="{{ route('backoffice.floors.destroy', ['tenant' => tenant('id'), 'floor' => $selected]) }}" method="POST" class="px-3 py-2">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger w-100">Delete Floor</button>
                                        </form>
                                    </li>
                                @endif
                            </ul>
                        </div>
                        @endif
                    </div>
                </div>
                <div>
                    <button id="addTableBtn" type="button" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-plus-lg me-1"></i> Add Table
                    </button>
                </div>
            </div>
            <div class="card card-white p-2">
                <div class="grid-stack" id="tablesGrid"
                     data-store-url="{{ route('backoffice.tables.store') }}"
                     data-positions-url="{{ route('backoffice.tables.positions') }}"
                     data-update-url-template="{{ rtrim(route('backoffice.tables.update', ['dining_table' => 0]), '0') }}"
                     data-destroy-url-template="{{ rtrim(route('backoffice.tables.destroy', ['dining_table' => 0]), '0') }}"
                     data-csrf="{{ csrf_token() }}"
                     data-floor-id="{{ $currentFloorId }}"
                >
                    @foreach($tables as $t)
                        <div class="grid-stack-item" data-id="{{ $t->id }}" data-label="{{ $t->label }}" data-status="{{ $t->status }}" data-shape="{{ $t->shape }}" data-capacity="{{ $t->capacity }}" data-color="{{ $t->color }}"
                             gs-x="{{ max(0, (int)($t->x ?? 0)) }}" gs-y="{{ max(0, (int)($t->y ?? 0)) }}" gs-w="{{ max(1, (int)($t->w ?: 2)) }}" gs-h="{{ max(1, (int)($t->h ?: 2)) }}">
                            @php
                                $status = strtolower($t->status);
                                $borderColor = match($status) {
                                    'available' => '#16a34a',
                                    'occupied' => '#dc2626',
                                    'oncleaning' => '#f59e0b',
                                    'archived' => '#111827',
                                    default => '#e2e8f0',
                                };
                            @endphp
                            <div class="grid-stack-item-content d-flex flex-column justify-content-center align-items-center"
                                 style="background: {{ $t->color ?: '#f1f5f9' }}; border: 2px solid {{ $borderColor }}; border-radius: {{ $t->shape === 'circle' ? '50%' : '0.5rem' }}; {{ $t->shape === 'circle' ? 'aspect-ratio:1/1;' : '' }}">
                                <div class="fw-semibold">{{ $t->label }}</div>
                                <div class="small text-muted">{{ ucfirst($t->status) }} {{ $t->capacity }} seats</div>
                                <button class="btn btn-sm btn-outline-secondary mt-2 edit-table-btn" data-id="{{ $t->id }}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="form-text text-muted mt-2 d-flex justify-content-between align-items-center mt-3">
                Drag and resize tables; click Save Layout to persist positions.
                <div>
                    <button id="saveLayoutBtn" type="button" class="btn btn-sm btn-success">
                        <i class="bi bi-save me-1"></i> Save Layout
                    </button>
            </div>
        </div>
    </div>

    <!-- Add Floor Modal -->
    <div class="modal fade" id="addFloorModal" tabindex="-1" aria-labelledby="addFloorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addFloorModalLabel">Add New Floor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('backoffice.floors.store') }}" method="POST">
                    @csrf
                    <div class="modal-body d-grid gap-3">
                        <div>
                            <label class="form-label">Floor Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Floor 2" required>
                        </div>
                        <div>
                            <label class="form-label">Area Type</label>
                            <input type="text" name="area_type" class="form-control" placeholder="indoor / outdoor">
                        </div>
                        <div>
                            <label class="form-label">Auto-create Tables (capacities)</label>
                            <input type="text" name="auto_tables" class="form-control" value="1,4,6">
                            <div class="form-text">Comma/space separated capacities. Defaults to 1,4,6.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Floor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite('resources/js/tables.js')
    @endpush
</x-backoffice.layout>


