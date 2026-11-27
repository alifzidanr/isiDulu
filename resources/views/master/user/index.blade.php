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
        {{-- Search by name or email as you type (?q=, see resources/js/live-search.js);
             keeps the chosen rows per page. --}}
        <form method="GET" action="{{ route('master.user.index') }}" role="search" class="border-b p-3 sm:p-4"
              x-data="liveSearch({ target: '#user-results' })" x-on:submit.prevent="run()">
            @if (request()->filled('per_page'))<input type="hidden" name="per_page" value="{{ request('per_page') }}">@endif
            <div class="relative w-full sm:max-w-xs">
                <span x-show="!busy" class="contents"><x-ui.icon name="search" class="text-muted-foreground pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2" /></span>
                <span x-show="busy" x-cloak class="contents"><x-ui.icon name="loader-circle" class="text-muted-foreground pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 animate-spin" /></span>
                <x-ui.input type="text" name="q" :value="$search" x-ref="input" x-model="q" x-on:input="schedule()"
                            placeholder="Cari nama atau email…" aria-label="Cari user" class="pr-8 pl-8" autocomplete="off" />
                <button type="button" x-show="q" x-cloak x-on:click="clear()" aria-label="Hapus pencarian"
                        class="text-muted-foreground hover:text-foreground absolute top-1/2 right-2 -translate-y-1/2 cursor-pointer rounded-sm p-0.5">
                    <x-ui.icon name="x" class="size-4" />
                </button>
            </div>
        </form>
        <div id="user-results">
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
                                {{-- min-w-40: on phones the other columns would otherwise squeeze this to nothing. --}}
                                <div class="grid min-w-40 max-w-72">
                                    <span class="truncate font-medium">{{ $user->nama_lengkap }}</span>
                                    <span class="text-muted-foreground truncate text-xs">{{ $user->email }}</span>
                                </div>
                                @if ($user->id_pegawai_kehadiran)
                                    {{-- Hidden on phones so the name and email get the room. --}}
                                    <span class="hidden sm:contents"><x-ui.badge variant="outline" title="Terhubung dengan Kehadiran: bisa masuk dengan fingerprint/Face ID"><x-ui.icon name="fingerprint" class="size-3" /> Kehadiran</x-ui.badge></span>
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
                            <div class="grid whitespace-nowrap">
                                <span>{{ $user->kampus->nama_kampus ?? '—' }}</span>
                                @if ($user->unit)<span class="text-muted-foreground text-xs">{{ $user->unit->nama_unit }}@if ($user->subUnit) &rsaquo; {{ $user->subUnit->nama_sub_unit }}@endif</span>@endif
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
                                    'id_sub_unit' => $user->id_sub_unit,
                                    'access_level' => $user->access_level,
                                    'id_pegawai_kehadiran' => $user->id_pegawai_kehadiran,
                                ]"
                                :destroy="$user->id_user !== auth()->id() ? route('master.user.destroy', $user->id_user) : null"
                                :confirm="'Hapus user “' . $user->nama_lengkap . '”?'"
                                :name="$user->nama_lengkap" />
                        </x-ui.table.cell>
                    </x-ui.table.row>
                @empty
                    @if ($search !== '')
                        <x-ui.table.empty colspan="5" title="Tidak ada user yang cocok" :description="'Tidak ditemukan nama atau email dengan “' . $search . '”.'" icon="search" />
                    @else
                        <x-ui.table.empty colspan="5" title="Belum ada user" icon="users" />
                    @endif
                @endforelse
            </x-ui.table.body>
        </x-ui.table>
        @if ($users->total() > 0)
            <div class="border-t px-6 py-4">{{ $users->links() }}</div>
        @endif
        </div>
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
                {{-- Only the chosen unit's sub units; cleared when the unit changes. --}}
                <x-ui.field label="Sub Unit" for="{{ $mode }}_sub_unit" name="id_sub_unit" :form="$mode"
                            x-data="{ subUnits: {{ Js::from($subUnits) }} }"
                            x-effect="if ({{ $model }}.id_sub_unit && !subUnits.some(s => String(s.id_sub_unit) === String({{ $model }}.id_sub_unit) && String(s.id_unit) === String({{ $model }}.id_unit))) {{ $model }}.id_sub_unit = ''">
                    <x-ui.select id="{{ $mode }}_sub_unit" name="id_sub_unit" x-model="{{ $model }}.id_sub_unit"
                                 x-bind:disabled="!{{ $model }}.id_unit" placeholder="Pilih sub unit">
                        <template x-for="s in subUnits.filter(s => String(s.id_unit) === String({{ $model }}.id_unit))" x-bind:key="s.id_sub_unit">
                            <option x-bind:value="s.id_sub_unit" x-text="s.nama_sub_unit" x-bind:selected="String(s.id_sub_unit) === String({{ $model }}.id_sub_unit)"></option>
                        </template>
                    </x-ui.select>
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
