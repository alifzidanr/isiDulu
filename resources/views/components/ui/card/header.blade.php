{{-- Card header. An optional `action` slot sits at the top right (e.g. a button). --}}
@props(['action' => null])
<div {{ $attributes->merge(['class' => 'flex flex-col gap-3 px-6 sm:flex-row sm:items-start sm:justify-between']) }}>
    <div class="grid gap-1.5">{{ $slot }}</div>
    @if ($action)
        <div class="flex shrink-0 items-center gap-2">{{ $action }}</div>
    @endif
</div>
