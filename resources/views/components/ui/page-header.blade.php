{{-- Page title row: <x-ui.page-header title="Master Kampus" description="..."><x-slot:actions>...</x-slot:actions></x-ui.page-header> --}}
@props(['title', 'description' => null, 'actions' => null])
<div {{ $attributes->merge(['class' => 'flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="grid gap-1">
        <h1 class="text-2xl font-semibold tracking-tight">{{ $title }}</h1>
        @if ($description)<p class="text-muted-foreground text-sm">{{ $description }}</p>@endif
    </div>
    @if ($actions)<div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>@endif
</div>
