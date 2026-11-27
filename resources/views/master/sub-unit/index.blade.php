@extends('layouts.app')

@section('title', 'Master Sub Unit - HelpDesk')
@section('page', 'Master Data / Sub Unit')

@php
    $unitOptions = $units->mapWithKeys(fn ($u) => [$u->id_unit => $u->nama_unit . ' — ' . ($u->kampus->nama_kampus ?? '-')]);
@endphp

@section('content')
<div class="grid gap-6"
     x-data="crud({ updateUrl: @js(route('master.sub-unit.update', '__ID__')), reopen: @js(old('_form')), old: @js(session()->getOldInput()) })">

    <x-ui.page-header title="Sub Unit" description="Bagian di dalam unit kerja.">
        <x-slot:actions>
            <x-ui.button icon="plus" x-on:click="openCreate()">Tambah Sub Unit</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card flush>
        <x-ui.table>
            <x-ui.table.header>
                <x-ui.table.head class="w-16">No</x-ui.table.head>
                <x-ui.table.head>Nama Sub Unit</x-ui.table.head>
                <x-ui.table.head>Unit</x-ui.table.head>
                <x-ui.table.head>Kampus</x-ui.table.head>
                <x-ui.table.head class="w-16 text-right"><span class="sr-only">Aksi</span></x-ui.table.head>
            </x-ui.table.header>
            <x-ui.table.body>
                @forelse ($subUnits as $index => $subUnit)
                    <x-ui.table.row>
                        <x-ui.table.cell class="text-muted-foreground tabular-nums">{{ $subUnits->firstItem() + $index }}</x-ui.table.cell>
                        <x-ui.table.cell class="font-medium">{{ $subUnit->nama_sub_unit }}</x-ui.table.cell>
                        <x-ui.table.cell>{{ $subUnit->unit->nama_unit ?? '—' }}</x-ui.table.cell>
                        <x-ui.table.cell>
                            @if ($subUnit->unit?->kampus)
                                <x-ui.badge variant="secondary">{{ $subUnit->unit->kampus->nama_kampus }}</x-ui.badge>
                            @else
                                <span class="text-muted-foreground">—</span>
                            @endif
                        </x-ui.table.cell>
                        <x-ui.table.cell class="text-right">
                            <x-row-actions
                                :edit="['id' => $subUnit->id_sub_unit, 'nama_sub_unit' => $subUnit->nama_sub_unit, 'id_unit' => $subUnit->id_unit]"
                                :destroy="route('master.sub-unit.destroy', $subUnit->id_sub_unit)"
                                :confirm="'Hapus sub unit “' . $subUnit->nama_sub_unit . '”?'"
                                :name="$subUnit->nama_sub_unit" />
                        </x-ui.table.cell>
                    </x-ui.table.row>
                @empty
                    <x-ui.table.empty colspan="5" title="Belum ada sub unit" description="Klik Tambah Sub Unit untuk membuat data pertama." icon="git-branch" />
                @endforelse
            </x-ui.table.body>
        </x-ui.table>
        @if ($subUnits->total() > 0)
            <div class="border-t px-6 py-4">{{ $subUnits->links() }}</div>
        @endif
    </x-ui.card>

    <x-ui.dialog model="createOpen" title="Tambah Sub Unit" size="sm">
        <form id="createForm" method="POST" action="{{ route('master.sub-unit.store') }}" class="grid gap-4">
            @csrf
            <input type="hidden" name="_form" value="create">
            <x-ui.field label="Nama Sub Unit" for="nama_sub_unit" name="nama_sub_unit" form="create" required>
                <x-ui.input id="nama_sub_unit" name="nama_sub_unit" x-model="form.nama_sub_unit" placeholder="Masukkan nama sub unit" required />
            </x-ui.field>
            <x-ui.field label="Unit" for="id_unit" name="id_unit" form="create" required>
                <x-ui.select id="id_unit" name="id_unit" x-model="form.id_unit" placeholder="Pilih unit" :options="$unitOptions" required />
            </x-ui.field>
        </form>
        <x-slot:footer>
            <x-ui.button variant="outline" x-on:click="createOpen = false">Batal</x-ui.button>
            <x-ui.button type="submit" form="createForm">Simpan</x-ui.button>
        </x-slot:footer>
    </x-ui.dialog>

    <x-ui.dialog model="editOpen" title="Edit Sub Unit" size="sm">
        <form id="editForm" method="POST" x-bind:action="editAction" class="grid gap-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="_form" x-bind:value="'edit:' + editing.id">
            <x-ui.field label="Nama Sub Unit" for="edit_nama_sub_unit" name="nama_sub_unit" form="edit" required>
                <x-ui.input id="edit_nama_sub_unit" name="nama_sub_unit" x-model="editing.nama_sub_unit" required />
            </x-ui.field>
            <x-ui.field label="Unit" for="edit_id_unit" name="id_unit" form="edit" required>
                <x-ui.select id="edit_id_unit" name="id_unit" x-model="editing.id_unit" placeholder="Pilih unit" :options="$unitOptions" required />
            </x-ui.field>
        </form>
        <x-slot:footer>
            <x-ui.button variant="outline" x-on:click="editOpen = false">Batal</x-ui.button>
            <x-ui.button type="submit" form="editForm">Simpan Perubahan</x-ui.button>
        </x-slot:footer>
    </x-ui.dialog>
</div>
@endsection
