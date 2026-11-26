{{--
    shadcn/ui Dialog, driven by Alpine. `model` is the Alpine expression that
    holds the open state in the surrounding x-data, e.g. model="createOpen".

    <x-ui.dialog model="createOpen" title="Tambah Kampus" description="...">
        ...form fields...
        <x-slot:footer>...buttons...</x-slot:footer>
    </x-ui.dialog>

    size: sm | md | lg | xl. Escape, clicking the overlay and the X all close it.
--}}
@props(['model', 'title' => null, 'description' => null, 'size' => 'md', 'footer' => null])

@php
    $widths = ['sm' => 'sm:max-w-md', 'md' => 'sm:max-w-lg', 'lg' => 'sm:max-w-2xl', 'xl' => 'sm:max-w-3xl'];
@endphp

<template x-teleport="body">
    <div x-show="{{ $model }}" x-cloak class="fixed inset-0 z-50" role="dialog" aria-modal="true"
         x-on:keydown.escape.window="{{ $model }} = false">
        <div x-show="{{ $model }}" x-transition.opacity.duration.150ms
             class="fixed inset-0 bg-black/50" x-on:click="{{ $model }} = false"></div>

        <div class="pointer-events-none fixed inset-0 flex items-end justify-center overflow-y-auto p-4 sm:items-center">
            <div x-show="{{ $model }}" x-trap.noscroll="{{ $model }}"
                 x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                 {{ $attributes->merge(['class' => 'bg-background pointer-events-auto relative grid max-h-[calc(100dvh-2rem)] w-full gap-4 overflow-y-auto rounded-lg border p-6 shadow-lg ' . ($widths[$size] ?? $widths['md'])]) }}>
                @if ($title || $description)
                    <div class="flex flex-col gap-2 pr-6 text-center sm:text-left">
                        @if ($title)<h2 class="text-lg leading-none font-semibold">{{ $title }}</h2>@endif
                        @if ($description)<p class="text-muted-foreground text-sm">{{ $description }}</p>@endif
                    </div>
                @endif

                {{ $slot }}

                @if ($footer)
                    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">{{ $footer }}</div>
                @endif

                <button type="button" x-on:click="{{ $model }} = false"
                        class="ring-offset-background focus:ring-ring absolute top-4 right-4 cursor-pointer rounded-xs opacity-70 transition-opacity hover:opacity-100 focus:ring-2 focus:ring-offset-2 focus:outline-hidden">
                    <x-ui.icon name="x" />
                    <span class="sr-only">Tutup</span>
                </button>
            </div>
        </div>
    </div>
</template>
