<div class="container-fluid p-0 notification-page" wire:poll.15s>
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <h3 class="m-0">Notifications</h3>
            <span class="badge bg-secondary">{{ $unreadCount }} unread</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    {{ $sort === 'recent' ? 'Recent' : 'Oldest' }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a wire:click.prevent="$set('sort','recent')" class="dropdown-item {{ $sort==='recent' ? 'active' : '' }}" href="#">Recent</a></li>
                    <li><a wire:click.prevent="$set('sort','oldest')" class="dropdown-item {{ $sort==='oldest' ? 'active' : '' }}" href="#">Oldest</a></li>
                </ul>
            </div>
            <div class="btn-group" role="group" aria-label="Status filter">
                <button wire:click="$set('status','all')" class="btn btn-outline-secondary {{ $status==='all' ? 'active' : '' }}">All</button>
                <button wire:click="$set('status','unread')" class="btn btn-outline-secondary {{ $status==='unread' ? 'active' : '' }}">Unread</button>
                <button wire:click="$set('status','read')" class="btn btn-outline-secondary {{ $status==='read' ? 'active' : '' }}">Read</button>
            </div>

        </div>
    </div>

    <div class="mb-3">
        <div class="mb-3">
            <button wire:click="$set('type','all')" class="btn {{ $type==='all' ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3">All</button>
            @foreach($types as $t)
                <button wire:click="$set('type','{{ $t }}')" class="btn {{ $type===$t ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3">
                    {{ \Illuminate\Support\Str::of($t)->replace(['.', '_'], ' ')->headline() }}
                </button>
            @endforeach
        </div>

        <div class="d-flex justify-content-between">
            <div class="input-group" style="max-width: 280px;">
                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control" placeholder="Search" wire:model.debounce.300ms="search">
            </div>
            <div>
                <button class="btn btn-dark" wire:click="markAllAsRead" @disabled($unreadCount===0)>
                    <i class="bi bi-check2-all me-1"></i> Mark all as read
                </button>
            </div>
        </div>
    </div>

    <div class="list-group list-group-flush border shadow mt-3">
        @forelse($notifications as $note)
            <div class="list-group-item py-3">
                <div class="d-flex gap-3 align-items-start">
                    <div class="flex-shrink-0">
                        <div class="rounded-circle note-icon d-inline-flex align-items-center justify-content-center" style="width:42px;height:42px;background:#e8f0fe;">
                            @php
                                $icon = match($note->type) {
                                    'order.created' => 'bi-bag-check',
                                    'tax.created' => 'bi-receipt-cutoff',
                                    'order.updated' => 'bi-receipt-cutoff',
                                    default => 'bi-bell'
                                };
                            @endphp
                            <i class="bi {{ $icon }} text-primary"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <div class="text-muted small">{{ \Illuminate\Support\Str::of($note->type)->replace(['.', '_'], ' ')->headline() }}</div>
                            <div class="text-muted small">{{ $note->created_at?->diffForHumans() }}</div>
                        </div>
                        <div class="d-flex align-items-start justify-content-between gap-2">
                            <div class="min-w-0">
                                <div class="{{ $note->is_read ? '' : 'fw-semibold' }}">{{ $note->title }}</div>
                                <div class="text-muted text-truncate-2 small">{{ $note->description }}</div>
                            </div>
                            <div class="ms-2 mt-1">
                                @if(!$note->is_read)
                                    <span class="badge bg-primary rounded-pill">new</span>
                                @endif
                            </div>
                        </div>
                        <div class="mt-2 d-flex gap-2">
                            <button class="btn btn-sm {{ $note->is_read ? 'btn-outline-secondary' : 'btn-outline-primary' }}" wire:click="toggleRead({{ $note->id }})">
                                <i class="bi {{ $note->is_read ? 'bi-check2-circle' : 'bi-circle' }} me-1"></i>
                                {{ $note->is_read ? 'Mark as unread' : 'Mark as read' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="list-group-item p-5 text-center text-muted">
                <i class="bi bi-inbox me-2"></i> No notifications found
            </div>
        @endforelse
    </div>
    @if($notifications->hasPages())
        <div class="card-footer bg-white">
            {{ $notifications->onEachSide(1)->links() }}
        </div>
    @endif
</div>
