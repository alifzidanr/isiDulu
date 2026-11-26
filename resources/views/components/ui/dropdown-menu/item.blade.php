{{-- Menu item. Renders a link when `href` is given, otherwise a button. variant: default | destructive --}}
@props(['icon' => null, 'href' => null, 'variant' => 'default'])
@php
    $classes = 'relative flex w-full cursor-pointer items-center gap-2 rounded-sm px-2 py-1.5 text-left text-sm outline-hidden select-none '
        . 'disabled:pointer-events-none disabled:opacity-50 [&_svg:not([class*=size-])]:size-4 '
        . ($variant === 'destructive'
            ? 'text-destructive hover:bg-destructive/10 focus:bg-destructive/10 [&_svg]:!text-destructive'
            : 'hover:bg-accent hover:text-accent-foreground focus:bg-accent [&_svg]:text-muted-foreground');
@endphp
@if ($href)
    <a href="{{ $href }}" data-menu-item {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<x-ui.icon :name="$icon" />@endif{{ $slot }}
    </a>
@else
    <button data-menu-item {{ $attributes->merge(['type' => 'button', 'class' => $classes]) }}>
        @if ($icon)<x-ui.icon :name="$icon" />@endif{{ $slot }}
    </button>
@endif
