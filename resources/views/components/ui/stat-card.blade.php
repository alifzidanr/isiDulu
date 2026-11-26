{{-- Dashboard metric card. --}}
@props(['title', 'value', 'icon' => null, 'hint' => null, 'tone' => 'text-muted-foreground'])
<x-ui.card flush {{ $attributes->merge(['class' => 'gap-3 py-6']) }}>
    <div class="flex items-center justify-between px-6">
        <p class="text-muted-foreground text-sm font-medium">{{ $title }}</p>
        @if ($icon)<x-ui.icon :name="$icon" class="size-4 {{ $tone }}" />@endif
    </div>
    <div class="px-6">
        <p class="text-3xl font-semibold tabular-nums">{{ $value }}</p>
        @if ($hint)<p class="text-muted-foreground mt-1 text-xs">{{ $hint }}</p>@endif
    </div>
</x-ui.card>
