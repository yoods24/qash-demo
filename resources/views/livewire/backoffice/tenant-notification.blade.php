<div class="position-relative tenant-notification" wire:poll.8s="refreshNotifications" data-lenis-prevent>
    <button class="sekunder nav-icon text-decoration-none" wire:click="toggle">
        <i class="bi bi-bell"></i>
        @if($unreadCount > 0)
            <span class="position-absolute translate-middle badge rounded-pill bg-danger">
                {{ $unreadCount }}
            </span>
        @endif
    </button>

        <div class="card shadow border-0 tenant-notification-card {{ $open ? 'is-open' : '' }}" aria-hidden="{{ $open ? 'false' : 'true' }}">
            <div class="card-body p-0">
                <div class="d-flex align-items-end justify-content-between text-center pt-3 px-3">
                    <div>
                        <h3 class="mb-0">Notifications</h3>
                    </div>
                    <button type="button" class="btn btn-sm rounded btn-outline-danger" wire:click="toggle"><i class="bi bi-x"></i></button>
                </div>
                    <hr class="mt-3 mb-0">

                <div class="d-flex flex-wrap align-items-center justify-content-between px-3">
                    <div class="d-flex gap-3 align-items-center small text-muted tenant-notification-link col-lg-5">
                        <a href="#" class="text-decoration-none {{ $tab==='all' ? 'fw-semibold active' : '' }}" wire:click.prevent="changeTab('all')">All</a>
                        <a href="#" class="text-decoration-none {{ $tab==='new' ? 'fw-semibold active' : '' }}" wire:click.prevent="changeTab('new')">
                            New 
                            @if($unreadCount) 
                            <span class="badge bg-danger count-icon ms-1">
                                {{ $unreadCount }}
                            </span> @endif
                        </a>
                        <a href="#" class="text-decoration-none {{ $tab==='read' ? 'fw-semibold active' : '' }}" wire:click.prevent="changeTab('read')">Read</a>
                    </div>
                    <div wire:click="markAllAsRead" class="d-flex align-items-center small text-muted col-lg-6 py-sm-3">
                        <p class="text-white m-0 btn-main btn btn-sm">
                            <span>
                                <i class="bi bi-check"></i>
                            </span>
                            Mark all as read
                        </p>
                    </div>
                </div>
                    <hr class="my-0">

                <div class="mt-2 tenant-notification-scroll">
                    @forelse($notifications as $note)
                        <div class="px-3 py-3 border-bottom d-flex gap-3 {{ $note->is_read ? '' : 'bg-light' }}">
                            <div class="flex-shrink-0">
                                <x-backoffice.notification.notification-icon :note=$note />
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="small text-muted">{{ ucfirst(str_replace(['.', '_'], ' ', $note->type)) }}</div>
                                    <div class="small text-muted">{{ $note->created_at?->diffForHumans() }}</div>
                                </div>
                                <div class="fw-semibold mt-1">{{ $note->title }}</div>
                                <div class="text-muted small">{{ $note->description }}</div>
                                <div class="mt-2 d-flex gap-2">
                                    <x-backoffice.notification.notification-link :note=$note :tenantId=$tenantId />
                                    @if(!$note->is_read)
                                        <button class="btn btn-sm btn-outline-secondary" wire:click="markAsRead({{ $note->id }})">Mark as read</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                    @empty
                        <div class="px-3 py-5 text-center text-muted">No notifications yet</div>
                    @endforelse
                </div>

                <div class="text-center mt-3 shadow-top">
                    <a href="{{route('backoffice.notification.index', ['tenant' => $tenantId])}}" class="w-100 btn btn-main rounded-0 text-decoration-none">
                        View All Notifications
                    </a>
                </div>
            </div>
        </div>
</div>
