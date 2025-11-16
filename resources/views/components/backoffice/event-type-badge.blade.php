@props(['type'])
@php
    $map = [
        'entertainment' => ['label' => 'Entertainment', 'icon' => 'bi-music-note-beamed', 'class' => 'event-type-badge--entertainment'],
        'announcement' => ['label' => 'Announcement', 'icon' => 'bi-megaphone', 'class' => 'event-type-badge--announcement'],
        'promotions' => ['label' => 'Promotions', 'icon' => 'bi-lightning-charge', 'class' => 'event-type-badge--promotions'],
        'special_event' => ['label' => 'Special Event', 'icon' => 'bi-stars', 'class' => 'event-type-badge--special-event'],
        'workshop' => ['label' => 'Workshop', 'icon' => 'bi-tools', 'class' => 'event-type-badge--workshop'],
        'community' => ['label' => 'Community', 'icon' => 'bi-people', 'class' => 'event-type-badge--community'],
        'operational' => ['label' => 'Operational', 'icon' => 'bi-gear', 'class' => 'event-type-badge--operational'],
    ];
    $meta = $map[$type] ?? ['label' => ucfirst(str_replace('_', ' ', $type ?? 'Event')), 'icon' => 'bi-calendar-event', 'class' => ''];
@endphp
<span class="event-type-badge {{ $meta['class'] }}">
    <i class="bi {{ $meta['icon'] }}"></i>
    <span>{{ $meta['label'] }}</span>
</span>
