@props(['active' => null])

@php
    $iconClass = $attributes->get('class');
    $link = $attributes->get('href');

    // Auto-detect active state
    if ($active === null && $link) {
        // Convert full URL → relative path
        $path = trim(str_replace(url('/'), '', $link), '/');

        $active = request()->is($path) || request()->is("$path/*");
    }

    $classes = $active ? 'nav-links active me-4' : 'nav-links me-4';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
