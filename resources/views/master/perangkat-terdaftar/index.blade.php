@extends('layouts.app')

@section('title', 'Master Perangkat Terdaftar - HelpDesk')
@section('page', 'Master Data / Perangkat Terdaftar')

@php
    $jenisOptions = $jenisPerangkats->pluck('nama_perangkat', 'id_jenis_perangkat');
    $inventarisOptions = ['y' => 'Inventaris', 'n' => 'Non-Inventaris'];
@endphp

@section('content')
<div class="grid gap-6"
     x-data="crud({ updateUrl: @js(route('master.perangkat-terdaftar.update', '__ID__')), reopen: @js(old('_form')), old: @js(session()->getOldInput()) })">

    <x-ui.page-header title="Perangkat Terdaftar" description="Perangkat yang tercatat beserta penggunanya.">
        <x-slot:actions>
            <x-ui.button icon="plus" x-on:click="openCreate()">Tambah Perangkat</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card flush>
        <x-ui.table>
            <x-ui.table.header>
                <x-ui.table.head class="w-16">No</x-ui.table.head>
                <x-ui.table.head>Nama Perangkat</x-ui.table.head>
                <x-ui.table.head>Jenis</x-ui.table.head>
                <x-ui.table.head>Pengguna</x-ui.table.head>
                <x-ui.table.head>Inventaris</x-ui.table.head>
                <x-ui.table.head class="w-16 text-right"><span class="sr-only">Aksi</span></x-ui.table.head>
            </x-ui.table.header>
            <x-ui.table.body>
                @forelse ($perangkatTerdaftars as $index => $perangkat)
                    <x-ui.table.row>
                        <x-ui.table.cell class="text-muted-foreground tabular-nums">{{ $perangkatTerdaftars->firstItem() + $index }}</x-ui.table.cell>
                        <x-ui.table.cell class="font-medium">{{ $perangkat->nama_perangkat_terdaftar }}</x-ui.table.cell>
                        <x-ui.table.cell>{{ $perangkat->jenisPerangkat->nama_perangkat ?? '—' }}</x-ui.table.cell>
                        <x-ui.table.cell>{{ $perangkat->pengguna ?: '—' }}</x-ui.table.cell>
                        <x-ui.table.cell>
                            <x-ui.badge :variant="$perangkat->inventaris === 'y' ? 'success' : 'muted'">
                                {{ $perangkat->inventaris === 'y' ? 'Inventaris' : 'Non-Inventaris' }}
                            </x-ui.badge>
                        </x-ui.table.cell>
                        <x-ui.table.cell class="text-right">
                            <x-row-actions
                                :edit="[
                                    'id' => $perangkat->id_perangkat_terdaftar,
                                    'nama_perangkat_terdaftar' => $perangkat->nama_perangkat_terdaftar,
                                    'id_jenis_perangkat' => $perangkat->id_jenis_perangkat,
                                    'pengguna' => $perangkat->pengguna,
                                    'inventaris' => $perangkat->inventaris,
                                ]"
                                :destroy="route('master.perangkat-terdaftar.destroy', $perangkat->id_perangkat_terdaftar)"
                                :confirm="'Hapus perangkat “' . $perangkat->nama_perangkat_terdaftar . '”?'"
                                :name="$perangkat->nama_perangkat_terdaftar" />
                        </x-ui.table.cell>
                    </x-ui.table.row>
                @empty
                    <x-ui.table.empty colspan="6" title="Belum ada perangkat terdaftar" description="Klik Tambah Perangkat untuk membuat data pertama." icon="clipboard-list" />
                @endforelse
            </x-ui.table.body>
        </x-ui.table>
        @if ($perangkatTerdaftars->total() > 0)
            <div class="border-t px-6 py-4">{{ $perangkatTerdaftars->links() }}</div>
        @endif
    </x-ui.card>

    @foreach (['create' => ['createOpen', 'form', 'Tambah Perangkat', 'Simpan'], 'edit' => ['editOpen', 'editing', 'Edit Perangkat', 'Simpan Perubahan']] as $mode => [$open, $model, $heading, $submit])
        <x-ui.dialog :model="$open" :title="$heading" size="lg">
            <form id="{{ $mode }}Form" method="POST" class="grid gap-4 sm:grid-cols-2"
                  @if ($mode === 'create') action="{{ route('master.perangkat-terdaftar.store') }}" @else x-bind:action="editAction" @endif>
                @csrf
                @if ($mode === 'edit')
                    @method('PUT')
                    <input type="hidden" name="_form" x-bind:value="'edit:' + editing.id">
                @else
                    <input type="hidden" name="_form" value="create">
                @endif
                <x-ui.field label="Nama Perangkat" for="{{ $mode }}_nama" name="nama_perangkat_terdaftar" :form="$mode" required>
                    <x-ui.input id="{{ $mode }}_nama" name="nama_perangkat_terdaftar" x-model="{{ $model }}.nama_perangkat_terdaftar" placeholder="Misal: Laptop Lab 01" required />
                </x-ui.field>
                <x-ui.field label="Jenis Perangkat" for="{{ $mode }}_jenis" name="id_jenis_perangkat" :form="$mode" required>
                    <x-ui.select id="{{ $mode }}_jenis" name="id_jenis_perangkat" x-model="{{ $model }}.id_jenis_perangkat" placeholder="Pilih jenis perangkat" :options="$jenisOptions" required />
                </x-ui.field>
                <x-ui.field label="Pengguna" for="{{ $mode }}_pengguna" name="pengguna" :form="$mode">
                    <x-ui.input id="{{ $mode }}_pengguna" name="pengguna" x-model="{{ $model }}.pengguna" placeholder="Nama pengguna (opsional)" />
                </x-ui.field>
                <x-ui.field label="Status Inventaris" for="{{ $mode }}_inventaris" name="inventaris" :form="$mode" required>
                    <x-ui.select id="{{ $mode }}_inventaris" name="inventaris" x-model="{{ $model }}.inventaris" placeholder="Pilih status" :options="$inventarisOptions" required />
                </x-ui.field>
            </form>
            <x-slot:footer>
                <x-ui.button variant="outline" x-on:click="{{ $open }} = false">Batal</x-ui.button>
                <x-ui.button type="submit" form="{{ $mode }}Form">{{ $submit }}</x-ui.button>
            </x-slot:footer>
        </x-ui.dialog>
    @endforeach
</div>
@endsection
