{{--
    shadcn/ui Button.
    <x-ui.button>Simpan</x-ui.button>
    <x-ui.button variant="outline" size="sm" href="/x" icon="plus">Tambah</x-ui.button>
    variant: default | destructive | outline | secondary | ghost | link
    size:    default | sm | lg | icon | icon-sm
--}}
@props([
    'variant' => 'default',
    'size' => 'default',
    'href' => null,
    'type' => 'button',
    'icon' => null,
])

@php
    $variants = [
        'default' => 'bg-primary text-primary-foreground shadow-xs hover:bg-primary/90',
        'destructive' => 'bg-destructive text-white shadow-xs hover:bg-destructive/90 focus-visible:ring-destructive/20',
        'outline' => 'border bg-background shadow-xs hover:bg-accent hover:text-accent-foreground',
        'secondary' => 'bg-secondary text-secondary-foreground shadow-xs hover:bg-secondary/80',
        'ghost' => 'hover:bg-accent hover:text-accent-foreground',
        'link' => 'text-primary underline-offset-4 hover:underline',
    ];
    $sizes = [
        'default' => 'h-9 px-4 py-2 has-[>svg]:px-3',
        'sm' => 'h-8 rounded-md gap-1.5 px-3 has-[>svg]:px-2.5',
        'lg' => 'h-10 rounded-md px-6 has-[>svg]:px-4',
        'icon' => 'size-9',
        'icon-sm' => 'size-8',
    ];
    $classes = 'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-all cursor-pointer '
        . 'disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg:not([class*=size-])]:size-4 shrink-0 '
        . 'outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] '
        . ($variants[$variant] ?? $variants['default']) . ' ' . ($sizes[$size] ?? $sizes['default']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<x-ui.icon :name="$icon" />@endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<x-ui.icon :name="$icon" />@endif
        {{ $slot }}
    </button>
@endif
