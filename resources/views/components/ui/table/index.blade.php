{{-- shadcn/ui Table. Compose with x-ui.table.header / body / row / head / cell / empty. --}}
<div class="relative w-full overflow-x-auto">
    <table {{ $attributes->merge(['class' => 'w-full caption-bottom text-sm']) }}>
        {{ $slot }}
    </table>
</div>
