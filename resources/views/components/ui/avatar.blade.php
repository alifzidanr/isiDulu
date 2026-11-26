{{-- Initials avatar: <x-ui.avatar name="Alif Zidan" /> --}}
@props(['name' => '', 'size' => 'size-8'])
@php
    $initials = collect(preg_split('/\s+/', trim((string) $name)))->filter()->take(2)
        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: '?';
@endphp
<span {{ $attributes->merge(['class' => "bg-primary text-primary-foreground relative flex {$size} shrink-0 items-center justify-center overflow-hidden rounded-full text-xs font-medium"]) }}>{{ $initials }}</span>
