@extends('layouts.app')

@section('title', 'Master Unit - HelpDesk')
@section('page', 'Master Data / Unit')

@php
    $kampusOptions = $kampuses->pluck('nama_kampus', 'id_kampus');
@endphp

@section('content')
<div class="grid gap-6"
     x-data="crud({ updateUrl: @js(route('master.unit.update', '__ID__')), reopen: @js(old('_form')), old: @js(session()->getOldInput()) })">

    <x-ui.page-header title="Unit" description="Unit kerja di setiap kampus.">
        <x-slot:actions>
            <x-ui.button icon="plus" x-on:click="openCreate()">Tambah Unit</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card flush>
        <x-ui.table>
            <x-ui.table.header>
                <x-ui.table.head class="w-16">No</x-ui.table.head>
                <x-ui.table.head>Nama Unit</x-ui.table.head>
                <x-ui.table.head>Kampus</x-ui.table.head>
                <x-ui.table.head class="w-16 text-right"><span class="sr-only">Aksi</span></x-ui.table.head>
            </x-ui.table.header>
            <x-ui.table.body>
                @forelse ($units as $index => $unit)
                    <x-ui.table.row>
                        <x-ui.table.cell class="text-muted-foreground tabular-nums">{{ $units->firstItem() + $index }}</x-ui.table.cell>
                        <x-ui.table.cell class="font-medium">{{ $unit->nama_unit }}</x-ui.table.cell>
                        <x-ui.table.cell>
                            @if ($unit->kampus)
                                <x-ui.badge variant="secondary">{{ $unit->kampus->nama_kampus }}</x-ui.badge>
                            @else
                                <span class="text-muted-foreground">—</span>
                            @endif
                        </x-ui.table.cell>
                        <x-ui.table.cell class="text-right">
                            <x-row-actions
                                :edit="['id' => $unit->id_unit, 'nama_unit' => $unit->nama_unit, 'id_kampus' => $unit->id_kampus]"
                                :destroy="route('master.unit.destroy', $unit->id_unit)"
                                :confirm="'Hapus unit “' . $unit->nama_unit . '”?'"
                                :name="$unit->nama_unit" />
                        </x-ui.table.cell>
                    </x-ui.table.row>
                @empty
                    <x-ui.table.empty colspan="4" title="Belum ada unit" description="Klik Tambah Unit untuk membuat data pertama." icon="network" />
                @endforelse
            </x-ui.table.body>
        </x-ui.table>
        @if ($units->total() > 0)
            <div class="border-t px-6 py-4">{{ $units->links() }}</div>
        @endif
    </x-ui.card>

    <x-ui.dialog model="createOpen" title="Tambah Unit" size="sm">
        <form id="createForm" method="POST" action="{{ route('master.unit.store') }}" class="grid gap-4">
            @csrf
            <input type="hidden" name="_form" value="create">
            <x-ui.field label="Nama Unit" for="nama_unit" name="nama_unit" form="create" required>
                <x-ui.input id="nama_unit" name="nama_unit" x-model="form.nama_unit" placeholder="Masukkan nama unit" required />
            </x-ui.field>
            <x-ui.field label="Kampus" for="id_kampus" name="id_kampus" form="create" required>
                <x-ui.select id="id_kampus" name="id_kampus" x-model="form.id_kampus" placeholder="Pilih kampus" :options="$kampusOptions" required />
            </x-ui.field>
        </form>
        <x-slot:footer>
            <x-ui.button variant="outline" x-on:click="createOpen = false">Batal</x-ui.button>
            <x-ui.button type="submit" form="createForm">Simpan</x-ui.button>
        </x-slot:footer>
    </x-ui.dialog>

    <x-ui.dialog model="editOpen" title="Edit Unit" size="sm">
        <form id="editForm" method="POST" x-bind:action="editAction" class="grid gap-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="_form" x-bind:value="'edit:' + editing.id">
            <x-ui.field label="Nama Unit" for="edit_nama_unit" name="nama_unit" form="edit" required>
                <x-ui.input id="edit_nama_unit" name="nama_unit" x-model="editing.nama_unit" required />
            </x-ui.field>
            <x-ui.field label="Kampus" for="edit_id_kampus" name="id_kampus" form="edit" required>
                <x-ui.select id="edit_id_kampus" name="id_kampus" x-model="editing.id_kampus" placeholder="Pilih kampus" :options="$kampusOptions" required />
            </x-ui.field>
        </form>
        <x-slot:footer>
            <x-ui.button variant="outline" x-on:click="editOpen = false">Batal</x-ui.button>
            <x-ui.button type="submit" form="editForm">Simpan Perubahan</x-ui.button>
        </x-slot:footer>
    </x-ui.dialog>
</div>
@endsection
