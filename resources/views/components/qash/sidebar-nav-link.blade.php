@props(['active' => null])
@php
    $iconClass = $attributes->get('class');
    $link = $attributes->get('href');

    // Auto-detect active state if not explicitly provided
    if ($active === null) {
        $path = $link ? ltrim(parse_url($link, PHP_URL_PATH) ?? '', '/') : '';
        $active = $path !== '' && (request()->is($path) || request()->is($path.'/*'));
    }

    $classes = $active ? 'nav-link active' : 'nav-link';
@endphp

<li class="nav-item">
  <a href="{{$link}}" class="{{$classes}}">
      <i class="{{$iconClass}}"></i>
      <span>{{$slot}}</span>
  </a>
  </li>
