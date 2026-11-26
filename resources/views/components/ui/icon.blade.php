{{-- Lucide icon (the icon set shadcn/ui uses). Names are kebab-case: <x-ui.icon name="trash-2" class="size-5" /> --}}
@props(['name'])
<i data-lucide="{{ $name }}" {{ $attributes->merge(['class' => 'size-4']) }}></i>
