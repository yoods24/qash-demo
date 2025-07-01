@props(['active'])

@php
    $classes = ($active ?? false)
                ? 'nav-links nav-links-active me-4'
                : 'nav-links me-4';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
