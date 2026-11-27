@extends('layouts.app')

@section('title', 'Master User - HelpDesk')
@section('page', 'Master Data / User')

@php
    $kampusOptions = $kampuses->pluck('nama_kampus', 'id_kampus');
    $unitOptions = $units->pluck('nama_unit', 'id_unit');
    $levelOptions = [0 => 'Superadmin', 1 => 'Admin', 2 => 'User'];
    $pegawaiOptions = $pegawaiKehadiran->mapWithKeys(fn ($p) => [$p->id_pegawai => $p->nama_lengkap . ($p->nip ? ' — ' . $p->nip : '')]);
@endphp

@section('content')
<div class="grid gap-6"
     x-data="crud({ updateUrl: @js(route('master.user.update', '__ID__')), defaults: { access_level: '2' }, reopen: @js(old('_form')), old: @js(session()->getOldInput()) })">

    <x-ui.page-header title="User" description="Akun yang bisa masuk ke panel HelpDesk.">
        <x-slot:actions>
            <x-ui.button icon="plus" x-on:click="openCreate()">Tambah User</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card flush>
        <x-ui.table>
            <x-ui.table.header>
                <x-ui.table.head>Nama</x-ui.table.head>
                <x-ui.table.head>Telegram ID</x-ui.table.head>
                <x-ui.table.head>Akses</x-ui.table.head>
                <x-ui.table.head>Kampus / Unit</x-ui.table.head>
                <x-ui.table.head class="w-16 text-right"><span class="sr-only">Aksi</span></x-ui.table.head>
            </x-ui.table.header>
            <x-ui.table.body>
                @forelse ($users as $user)
                    <x-ui.table.row>
                        <x-ui.table.cell>
                            <div class="flex items-center gap-3">
                                <x-ui.avatar :name="$user->nama_lengkap" />
                                <div class="grid min-w-0">
                                    <span class="truncate font-medium">{{ $user->nama_lengkap }}</span>
                                    <span class="text-muted-foreground truncate text-xs">{{ $user->email }}</span>
                                </div>
                                @if ($user->id_pegawai_kehadiran)
                                    <x-ui.badge variant="outline" title="Terhubung dengan Kehadiran: bisa masuk dengan fingerprint/Face ID"><x-ui.icon name="fingerprint" class="size-3" /> Kehadiran</x-ui.badge>
                                @endif
                            </div>
                        </x-ui.table.cell>
                        <x-ui.table.cell class="font-mono text-xs">{{ $user->telegram_id ?: '—' }}</x-ui.table.cell>
                        <x-ui.table.cell>
                            <x-ui.badge :variant="$user->access_level == 0 ? 'default' : ($user->access_level == 1 ? 'info' : 'outline')">
                                {{ $levelOptions[$user->access_level] ?? 'User' }}
                            </x-ui.badge>
                        </x-ui.table.cell>
                        <x-ui.table.cell>
                            <div class="grid">
                                <span>{{ $user->kampus->nama_kampus ?? '—' }}</span>
                                @if ($user->unit)<span class="text-muted-foreground text-xs">{{ $user->unit->nama_unit }}</span>@endif
                            </div>
                        </x-ui.table.cell>
                        <x-ui.table.cell class="text-right">
                            <x-row-actions
                                :edit="[
                                    'id' => $user->id_user,
                                    'nama_lengkap' => $user->nama_lengkap,
                                    'email' => $user->email,
                                    'telegram_id' => $user->telegram_id,
                                    'id_kampus' => $user->id_kampus,
                                    'id_unit' => $user->id_unit,
                                    'access_level' => $user->access_level,
                                    'id_pegawai_kehadiran' => $user->id_pegawai_kehadiran,
                                ]"
                                :destroy="$user->id_user !== auth()->id() ? route('master.user.destroy', $user->id_user) : null"
                                :confirm="'Hapus user “' . $user->nama_lengkap . '”?'" />
                        </x-ui.table.cell>
                    </x-ui.table.row>
                @empty
                    <x-ui.table.empty colspan="5" title="Belum ada user" icon="users" />
                @endforelse
            </x-ui.table.body>
        </x-ui.table>
        @if ($users->hasPages())
            <div class="border-t px-6 py-4">{{ $users->links() }}</div>
        @endif
    </x-ui.card>

    @foreach (['create' => ['createOpen', 'form', 'Tambah User', 'Simpan'], 'edit' => ['editOpen', 'editing', 'Edit User', 'Simpan Perubahan']] as $mode => [$open, $model, $heading, $submit])
        <x-ui.dialog :model="$open" :title="$heading" size="lg">
            <form id="{{ $mode }}Form" method="POST" class="grid gap-4 sm:grid-cols-2"
                  @if ($mode === 'create') action="{{ route('master.user.store') }}" @else x-bind:action="editAction" @endif>
                @csrf
                @if ($mode === 'edit')
                    @method('PUT')
                    <input type="hidden" name="_form" x-bind:value="'edit:' + editing.id">
                @else
                    <input type="hidden" name="_form" value="create">
                @endif
                <x-ui.field label="Nama Lengkap" for="{{ $mode }}_nama" name="nama_lengkap" :form="$mode" required>
                    <x-ui.input id="{{ $mode }}_nama" name="nama_lengkap" x-model="{{ $model }}.nama_lengkap" placeholder="Masukkan nama lengkap" required />
                </x-ui.field>
                <x-ui.field label="Email" for="{{ $mode }}_email" name="email" :form="$mode" required>
                    <x-ui.input type="email" id="{{ $mode }}_email" name="email" x-model="{{ $model }}.email" placeholder="user@example.com" required />
                </x-ui.field>
                <x-ui.field label="Telegram ID" for="{{ $mode }}_telegram" name="telegram_id" :form="$mode" hint="Opsional, untuk notifikasi Telegram.">
                    <x-ui.input id="{{ $mode }}_telegram" name="telegram_id" x-model="{{ $model }}.telegram_id" placeholder="Misal: 123456789" />
                </x-ui.field>
                <x-ui.field label="Password" for="{{ $mode }}_password" name="password" :form="$mode" :required="$mode === 'create'"
                            :hint="$mode === 'edit' ? 'Kosongkan jika tidak ingin mengubah.' : 'Minimal 6 karakter.'">
                    <x-ui.input type="password" id="{{ $mode }}_password" name="password" autocomplete="new-password"
                                :placeholder="$mode === 'edit' ? 'Password baru' : 'Masukkan password'" :required="$mode === 'create'" />
                </x-ui.field>
                <x-ui.field label="Access Level" for="{{ $mode }}_level" name="access_level" :form="$mode" required>
                    <x-ui.select id="{{ $mode }}_level" name="access_level" x-model="{{ $model }}.access_level" :options="$levelOptions" required />
                </x-ui.field>
                <x-ui.field label="Kampus" for="{{ $mode }}_kampus" name="id_kampus" :form="$mode">
                    <x-ui.select id="{{ $mode }}_kampus" name="id_kampus" x-model="{{ $model }}.id_kampus" placeholder="Pilih kampus" :options="$kampusOptions" />
                </x-ui.field>
                <x-ui.field label="Unit" for="{{ $mode }}_unit" name="id_unit" :form="$mode">
                    <x-ui.select id="{{ $mode }}_unit" name="id_unit" x-model="{{ $model }}.id_unit" placeholder="Pilih unit" :options="$unitOptions" />
                </x-ui.field>
                <x-ui.field label="Pegawai Kehadiran" for="{{ $mode }}_pegawai" name="id_pegawai_kehadiran" :form="$mode" class="sm:col-span-2"
                            hint="Untuk masuk dengan fingerprint/Face ID dari Kehadiran. Hanya pegawai Direktorat TI dan TD.">
                    <x-ui.select id="{{ $mode }}_pegawai" name="id_pegawai_kehadiran" x-model="{{ $model }}.id_pegawai_kehadiran" placeholder="Tidak terhubung" :options="$pegawaiOptions" />
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
