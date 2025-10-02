@props(['note', 'tenantId'])

@php
    // Accept either an array-casted attribute or a raw JSON string
    $params = is_array($note->route_params)
        ? $note->route_params
        : (json_decode($note->route_params ?? '[]', true) ?: []);
@endphp

<a href="{{ $note->route_name ? route($note->route_name, $params) : '#' }}" class="btn btn-sm btn-primary">
    View {{ $note->type }}
</a>
