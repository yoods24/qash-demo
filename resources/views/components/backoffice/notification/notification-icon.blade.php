@props(['note'])

@php
    $bgColor = 'bg-secondary';
    $iconType = 'bi-bell text-secondary';

    if (str_contains($note->type, 'order')) {
        $bgColor = 'bg-warning';
        $iconType = 'bi-fork-knife text-white';
    } elseif (str_contains($note->type, 'product')) {
        $bgColor = 'bg-success';
        $iconType = 'bi-box-seam text-white';
    } elseif (str_contains($note->type, 'warning')) {
        $bgColor = 'bg-warning';
        $iconType = 'bi-exclamation-triangle text-dark';
    }
@endphp

<div class="rounded-circle {{ $bgColor }} d-inline-flex align-items-center justify-content-center"
     style="width: 36px; height: 36px;">
    <i class="bi {{ $iconType }}"></i>
</div>
