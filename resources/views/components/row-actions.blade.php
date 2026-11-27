{{--
    The "..." menu on a master-data table row: Edit opens the page's edit
    dialog with `edit` as its data, Hapus asks for confirmation in a toast and
    then submits a DELETE to `destroy`. With `name`, the admin has to type that
    name exactly before Hapus is enabled (the controller checks it too, via
    confirm_name). Needs the crud() Alpine data on the page.
--}}
@props(['edit' => null, 'destroy' => null, 'confirm' => 'Hapus data ini?', 'name' => null])

<x-ui.dropdown-menu align="end" width="w-40">
    <x-slot:trigger>
        <x-ui.button variant="ghost" size="icon-sm" icon="ellipsis">
            <span class="sr-only">Buka aksi</span>
        </x-ui.button>
    </x-slot:trigger>
    @if ($edit)
        <x-ui.dropdown-menu.item icon="pencil" x-on:click="openEdit({{ Illuminate\Support\Js::from($edit) }})">Edit</x-ui.dropdown-menu.item>
    @endif
    {{ $slot }}
    @if ($destroy)
        <x-ui.dropdown-menu.separator />
        <form method="POST" action="{{ $destroy }}" x-ref="deleteForm">
            @csrf
            @method('DELETE')
            <input type="hidden" name="confirm_name">
            <x-ui.dropdown-menu.item variant="destructive" icon="trash-2"
                x-on:click="confirmDelete($el.closest('form'), {{ Illuminate\Support\Js::from($confirm) }}, null, {{ Illuminate\Support\Js::from($name) }})">Hapus</x-ui.dropdown-menu.item>
        </form>
    @endif
</x-ui.dropdown-menu>
