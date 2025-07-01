@props(['active'])
@php
$classes = ($active ?? false)
            ? 'nav-link active'
            : 'nav-link';
$iconClass = $attributes->get('class');
$link = $attributes->get('href');
@endphp

<li class="nav-item">
<a href="{{$link}}" class="{{$classes}}">
    <i class="{{$iconClass}}"></i>
    <span>{{$slot}}</span></a>
</li>