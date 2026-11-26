{{-- shadcn/ui Label. --}}
@props(['required' => false])
<label {{ $attributes->merge(['class' => 'flex items-center gap-1 text-sm leading-none font-medium select-none']) }}>
    {{ $slot }}
    @if ($required)<span class="text-destructive">*</span>@endif
</label>
