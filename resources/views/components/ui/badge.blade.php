{{--
    shadcn/ui Badge.
    variant: default | secondary | outline | destructive  (shadcn)
             success | warning | info | purple | muted     (soft status colours)
--}}
@props(['variant' => 'default'])

@php
    $variants = [
        'default' => 'border-transparent bg-primary text-primary-foreground',
        'secondary' => 'border-transparent bg-secondary text-secondary-foreground',
        'outline' => 'text-foreground',
        'destructive' => 'border-transparent bg-destructive text-white',
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-700',
        'info' => 'border-blue-200 bg-blue-50 text-blue-700',
        'purple' => 'border-violet-200 bg-violet-50 text-violet-700',
        'danger' => 'border-red-200 bg-red-50 text-red-700',
        'muted' => 'border-transparent bg-muted text-muted-foreground',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex w-fit shrink-0 items-center justify-center gap-1 overflow-hidden rounded-md border px-2 py-0.5 text-xs font-medium whitespace-nowrap [&>svg]:pointer-events-none [&>svg]:size-3 ' . ($variants[$variant] ?? $variants['default'])]) }}>
    {{ $slot }}
</span>
