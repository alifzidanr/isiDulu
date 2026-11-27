{{-- shadcn/ui Alert. variant: default | destructive | warning --}}
@props(['variant' => 'default', 'icon' => null, 'title' => null])
@php
    $variants = [
        'default' => 'bg-card text-card-foreground',
        'destructive' => 'text-destructive bg-card [&>p]:text-destructive/90',
        'warning' => 'border-amber-300 bg-amber-50 text-amber-900',
    ];
@endphp
<div role="alert" {{ $attributes->merge(['class' => 'relative flex w-full items-start gap-3 rounded-lg border px-4 py-3 text-sm ' . ($variants[$variant] ?? $variants['default'])]) }}>
    @if ($icon)<x-ui.icon :name="$icon" class="mt-0.5 size-4" />@endif
    <div class="grid gap-1">
        @if ($title)<div class="font-medium">{{ $title }}</div>@endif
        <div class="text-sm">{{ $slot }}</div>
    </div>
</div>
