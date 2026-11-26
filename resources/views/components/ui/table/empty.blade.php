{{-- Empty state row: <x-ui.table.empty colspan="4" title="Belum ada data" /> --}}
@props(['colspan' => 1, 'title' => 'Belum ada data', 'description' => null, 'icon' => 'inbox'])
<tr {{ $attributes }}>
    <td colspan="{{ $colspan }}" class="px-6 py-12">
        <div class="flex flex-col items-center justify-center gap-2 text-center">
            <div class="bg-muted flex size-10 items-center justify-center rounded-full">
                <x-ui.icon :name="$icon" class="text-muted-foreground size-5" />
            </div>
            <p class="text-sm font-medium">{{ $title }}</p>
            @if ($description)<p class="text-muted-foreground text-sm">{{ $description }}</p>@endif
            {{ $slot }}
        </div>
    </td>
</tr>
