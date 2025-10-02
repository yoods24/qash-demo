<?php

namespace App\Livewire\Backoffice;

use Livewire\Component;
use App\Models\TenantNotification as TenantNotificationModel;

class TenantNotification extends Component
{
    public $open = false;
    public $tab = 'all'; // all | new | read
    public $notifications = [];
    public $unreadCount = 0;
    public $tenantId = null;

    public function mount(): void
    {
        $this->tenantId = tenant()?->id ?? request()->route('tenant');
        $this->refreshNotifications();
    }

    public function toggle(): void
    {
        $this->open = !$this->open;
    }

    public function changeTab(string $tab): void
    {
        $this->tab = in_array($tab, ['all', 'new', 'read'], true) ? $tab : 'all';
        $this->refreshNotifications();
    }

    public function refreshNotifications(): void
    {
        $tenantId = $this->tenantId ?? tenant()?->id ?? request()->route('tenant');
        if (!$tenantId) {
            $this->notifications = [];
            $this->unreadCount = 0;
            return;
        }

        $query = TenantNotificationModel::query()
            ->where('tenant_id', $tenantId)
            ->latest();

        if ($this->tab === 'new') {
            $query->where('is_read', false);
        } elseif ($this->tab === 'read') {
            $query->where('is_read', true);
        }

        $this->notifications = $query->limit(10)->get();
        $this->unreadCount = TenantNotificationModel::query()
            ->where('tenant_id', $tenantId)
            ->where('is_read', false)
            ->count();
    }

    public function markAsRead(int $id): void
    {
        $tenantId = $this->tenantId ?? tenant()?->id ?? request()->route('tenant');
        if (!$tenantId) return;

        TenantNotificationModel::query()
            ->where('tenant_id', $tenantId)
            ->where('id', $id)
            ->update(['is_read' => true]);

        $this->refreshNotifications();
    }

    public function markAllAsRead(): void
    {
        $tenantId = $this->tenantId ?? tenant()?->id ?? request()->route('tenant');
        if (!$tenantId) return;

        TenantNotificationModel::query()
            ->where('tenant_id', $tenantId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $this->refreshNotifications();
    }

    public function render()
    {
        return view('livewire.backoffice.tenant-notification');
    }
}
