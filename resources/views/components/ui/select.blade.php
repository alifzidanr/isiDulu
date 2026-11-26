{{--
    Native <select> styled like shadcn/ui's Select trigger. A native control keeps
    forms, validation and mobile pickers working without extra JavaScript.
    <x-ui.select name="id_unit" placeholder="Pilih Unit" :options="[1 => 'A']" />
    or pass <option> elements in the slot.
--}}
@props(['invalid' => false, 'placeholder' => null, 'options' => null, 'selected' => null])
<div class="relative w-full">
    <select {{ $attributes->merge([
        'aria-invalid' => $invalid ? 'true' : null,
        'class' => 'border-input flex h-9 w-full appearance-none items-center rounded-md border bg-transparent py-1 pr-9 pl-3 text-base shadow-xs '
            . 'transition-[color,box-shadow] outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm '
            . 'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] '
            . 'aria-invalid:ring-destructive/20 aria-invalid:border-destructive',
    ]) }}>
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @if ($options !== null)
            @foreach ($options as $value => $label)
                <option value="{{ $value }}" @selected((string) $selected === (string) $value)>{{ $label }}</option>
            @endforeach
        @endif
        {{ $slot }}
    </select>
    <x-ui.icon name="chevron-down" class="text-muted-foreground pointer-events-none absolute top-1/2 right-3 size-4 -translate-y-1/2 opacity-50" />
</div>
