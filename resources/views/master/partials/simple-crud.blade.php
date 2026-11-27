{{--
    A master-data page for a table with a single name column (Kampus, Jenis
    Perangkat, Jenis Perawatan, Detail Perawatan).

    @include('master.partials.simple-crud', [
        'items' => $kampuses,          // paginator
        'title' => 'Kampus',           // page title
        'noun' => 'kampus',            // used in sentences ("Tambah kampus")
        'description' => '...',
        'route' => 'master.kampus',    // route name prefix (.store / .update / .destroy)
        'idField' => 'id_kampus',
        'field' => 'nama_kampus',
        'label' => 'Nama Kampus',
        'icon' => 'building-2',
    ])
--}}
<div class="grid gap-6"
     x-data="crud({ updateUrl: @js(route($route . '.update', '__ID__')), reopen: @js(old('_form')), old: @js(session()->getOldInput()) })">

    <x-ui.page-header :title="$title" :description="$description">
        <x-slot:actions>
            <x-ui.button icon="plus" x-on:click="openCreate()">Tambah {{ $title }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card flush>
        <x-ui.table>
            <x-ui.table.header>
                <x-ui.table.head class="w-16">No</x-ui.table.head>
                <x-ui.table.head>{{ $label }}</x-ui.table.head>
                <x-ui.table.head class="w-16 text-right"><span class="sr-only">Aksi</span></x-ui.table.head>
            </x-ui.table.header>
            <x-ui.table.body>
                @forelse ($items as $index => $item)
                    <x-ui.table.row>
                        <x-ui.table.cell class="text-muted-foreground tabular-nums">{{ $items->firstItem() + $index }}</x-ui.table.cell>
                        <x-ui.table.cell class="font-medium">{{ $item->{$field} }}</x-ui.table.cell>
                        <x-ui.table.cell class="text-right">
                            <x-row-actions
                                :edit="['id' => $item->{$idField}, $field => $item->{$field}]"
                                :destroy="route($route . '.destroy', $item->{$idField})"
                                :confirm="'Hapus ' . $noun . ' “' . $item->{$field} . '”?'" />
                        </x-ui.table.cell>
                    </x-ui.table.row>
                @empty
                    <x-ui.table.empty colspan="3" :title="'Belum ada data ' . $noun" description="Klik tombol Tambah untuk membuat data pertama." :icon="$icon" />
                @endforelse
            </x-ui.table.body>
        </x-ui.table>
        @if ($items->total() > 0)
            <div class="border-t px-6 py-4">{{ $items->links() }}</div>
        @endif
    </x-ui.card>

    <x-ui.dialog model="createOpen" :title="'Tambah ' . $title" size="sm">
        <form id="createForm" method="POST" action="{{ route($route . '.store') }}" class="grid gap-4">
            @csrf
            <input type="hidden" name="_form" value="create">
            <x-ui.field :label="$label" for="create_{{ $field }}" :name="$field" form="create" required>
                <x-ui.input id="create_{{ $field }}" :name="$field" x-model="form.{{ $field }}" :placeholder="'Masukkan ' . strtolower($label)" required />
            </x-ui.field>
        </form>
        <x-slot:footer>
            <x-ui.button variant="outline" x-on:click="createOpen = false">Batal</x-ui.button>
            <x-ui.button type="submit" form="createForm">Simpan</x-ui.button>
        </x-slot:footer>
    </x-ui.dialog>

    <x-ui.dialog model="editOpen" :title="'Edit ' . $title" size="sm">
        <form id="editForm" method="POST" x-bind:action="editAction" class="grid gap-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="_form" x-bind:value="'edit:' + editing.id">
            <x-ui.field :label="$label" for="edit_{{ $field }}" :name="$field" form="edit" required>
                <x-ui.input id="edit_{{ $field }}" :name="$field" x-model="editing.{{ $field }}" required />
            </x-ui.field>
        </form>
        <x-slot:footer>
            <x-ui.button variant="outline" x-on:click="editOpen = false">Batal</x-ui.button>
            <x-ui.button type="submit" form="editForm">Simpan Perubahan</x-ui.button>
        </x-slot:footer>
    </x-ui.dialog>
</div>
