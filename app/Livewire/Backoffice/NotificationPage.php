<?php

namespace App\Livewire\Backoffice;

use App\Models\TenantNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationPage extends Component
{
    use WithPagination;

    public string $tenantId = '';

    // Filters / UI state
    public string $type = 'all'; // dynamic types + 'all'
    public string $status = 'all'; // all|unread|read
    public string $sort = 'recent'; // recent|oldest
    public string $search = '';
    public int $perPage = 10;

    public int $unreadCount = 0;

    protected $queryString = [
        'type' => ['except' => 'all'],
        'status' => ['except' => 'all'],
        'sort' => ['except' => 'recent'],
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount(): void
    {
        $this->tenantId = (string) (tenant()?->id ?? request()->route('tenant'));
        $this->refreshUnreadCount();
    }

    public function updating($name): void
    {
        if (in_array($name, ['type', 'status', 'sort', 'search'], true)) {
            $this->resetPage();
        }
    }

    public function markAsRead(int $id): void
    {
        if (!$this->tenantId) return;

        TenantNotification::where('tenant_id', $this->tenantId)
            ->where('id', $id)
            ->update(['is_read' => true]);

        $this->refreshUnreadCount();
    }

    public function markAllAsRead(): void
    {
        if (!$this->tenantId) return;

        TenantNotification::where('tenant_id', $this->tenantId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $this->refreshUnreadCount();
    }

    public function toggleRead(int $id): void
    {
        if (!$this->tenantId) return;
        $note = TenantNotification::where('tenant_id', $this->tenantId)->find($id);
        if (!$note) return;
        $note->is_read = !$note->is_read;
        $note->save();
        $this->refreshUnreadCount();
    }

    protected function refreshUnreadCount(): void
    {
        if (!$this->tenantId) { $this->unreadCount = 0; return; }
        $this->unreadCount = TenantNotification::where('tenant_id', $this->tenantId)
            ->where('is_read', false)
            ->count();
    }

    public function getTypesProperty(): array
    {
        if (!$this->tenantId) return [];
        return TenantNotification::where('tenant_id', $this->tenantId)
            ->select('type')
            ->distinct()
            ->pluck('type')
            ->filter()
            ->values()
            ->all();
    }

    public function getNotificationsProperty(): LengthAwarePaginator
    {
        $q = TenantNotification::query()
            ->where('tenant_id', $this->tenantId);

        if ($this->type !== 'all') {
            $q->where('type', $this->type);
        }

        if ($this->status === 'unread') {
            $q->where('is_read', false);
        } elseif ($this->status === 'read') {
            $q->where('is_read', true);
        }

        if ($this->search !== '') {
            $q->where(function ($qq) {
                $qq->where('title', 'like', '%' . $this->search . '%')
                   ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        $q->orderBy('created_at', $this->sort === 'recent' ? 'desc' : 'asc');

        return $q->paginate($this->perPage);
    }

    public function render()
    {
        $this->refreshUnreadCount();
        return view('livewire.backoffice.notification-page', [
            'types' => $this->types,
            'notifications' => $this->notifications,
        ]);
    }
}
