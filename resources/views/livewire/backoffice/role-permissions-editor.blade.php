<div class="mb-3">
    <div class="container">
        @if (session('message'))
            <div class="alert alert-success mb-3">{{ session('message') }}</div>
        @endif

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div>
                <h2 class="">Manage Permissions</h2>
                <input type="text" class="form-control" style="max-width:240px" wire:model.live="roleName" placeholder="Role name" />
            </div>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <button type="button" class="btn btn-outline-danger" wire:click="disableAll" title="Disable all permissions">Disable All</button>
                <button type="button" class="btn btn-outline-success" wire:click="enableAll" title="Enable all permissions">Enable All</button>
            </div>
        </div>

        <div class="row g-3">
            @foreach ($modules as $module)
                @php
                    $mView = $module['view'];
                    $modEnabled = $state[$mView] ?? false;
                @endphp
                <div class="col-12">
                    <div class="card shadow-sm border-0 permission-card">
                        <div class="card-header bg-body d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-semibold">{{ $module['label'] }}</span>
                                <span class="text-muted small">({{ $mView }})</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="sw-{{ $mView }}" wire:model.live="state.{{ $mView }}">
                                    <label class="form-check-label" for="sw-{{ $mView }}">View</label>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="enableModule('{{ $module['key'] }}')" title="Enable all in {{ $module['label'] }}">Enable All</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                @foreach ($module['children'] as $child)
                                    @php
                                        $cView = $child['view'];
                                        $childEnabled = $state[$cView] ?? false;
                                    @endphp
                                    <div class="col-12 col-md-6 col-xl-4">
                                        <div class="p-3 border rounded-3 h-100 sub-card {{ $modEnabled ? '' : 'bg-light-subtle' }}">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold">{{ $child['label'] }}</span>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="form-check form-switch m-0">
                                                        <input class="form-check-input" type="checkbox" id="sw-{{ $cView }}" wire:model.live="state.{{ $cView }}" @disabled(!$modEnabled)>
                                                        <label class="form-check-label" for="sw-{{ $cView }}">View</label>
                                                    </div>
                                                </div>
                                            </div>
                                            @if (!empty($child['actions']))
                                                <div class="d-flex flex-column gap-2 mt-2">
                                                    @foreach ($child['actions'] as $action)
                                                        @php $aName = $action['name']; @endphp
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <div class="text-muted small">{{ $action['label'] }}</div>
                                                            <div class="form-check form-switch m-0">
                                                                <input class="form-check-input" type="checkbox" id="sw-{{ $aName }}" wire:model.live="state.{{ $aName }}" @disabled(!($modEnabled && $childEnabled))>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="pb-5"><!-- spacer to avoid overlap with sticky bar --></div>
    </div>

    <div class="permission-sticky-save shadow-lg shadow-sm">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="text-muted small">Review changes, then save or cancel.</div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#cancelModal">Cancel Changes</button>
                <button type="button" class="btn btn-primer" wire:click="save">Save Changes</button>
            </div>
        </div>
    </div>

</div>