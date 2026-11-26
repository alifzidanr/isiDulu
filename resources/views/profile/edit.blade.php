@extends('layouts.app')

@section('title', 'Pengaturan Profil - HelpDesk')
@section('page', 'Akun / Pengaturan Profil')

@php
    $roles = [0 => 'Superadmin', 1 => 'Admin', 2 => 'User'];
    $pegawai = $user->kehadiranPegawai;
    $passkeyCount = $user->passkeys->count();
@endphp

@section('content')
<div class="mx-auto grid max-w-2xl gap-6">
    <x-ui.page-header title="Pengaturan Profil" description="Kelola data akun dan kata sandi Anda." />

    {{-- Account --}}
    <x-ui.card>
        <x-ui.card.header>
            <x-ui.card.title>Informasi Akun</x-ui.card.title>
            <x-ui.card.description>Hak akses, kampus dan unit diatur oleh superadmin.</x-ui.card.description>
        </x-ui.card.header>
        <x-ui.card.content>
            <div class="flex items-center gap-4">
                <x-ui.avatar :name="$user->nama_lengkap" size="size-12" class="text-sm" />
                <div class="grid min-w-0 gap-1">
                    <p class="truncate font-medium">{{ $user->nama_lengkap }}</p>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <x-ui.badge variant="info">{{ $roles[$user->access_level] ?? 'User' }}</x-ui.badge>
                        <x-ui.badge :variant="$user->status === 'active' ? 'success' : 'muted'">{{ ucfirst($user->status ?? 'active') }}</x-ui.badge>
                    </div>
                </div>
            </div>
            <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="grid gap-1">
                    <dt class="text-muted-foreground text-sm">Kampus</dt>
                    <dd class="text-sm font-medium">{{ $user->kampus->nama_kampus ?? '—' }}</dd>
                </div>
                <div class="grid gap-1">
                    <dt class="text-muted-foreground text-sm">Unit</dt>
                    <dd class="text-sm font-medium">{{ $user->unit->nama_unit ?? '—' }}</dd>
                </div>
            </dl>
        </x-ui.card.content>
    </x-ui.card>

    {{-- Contact --}}
    <x-ui.card>
        <x-ui.card.header>
            <x-ui.card.title>Informasi Kontak</x-ui.card.title>
            <x-ui.card.description>Email dipakai untuk masuk, Telegram ID untuk notifikasi permohonan.</x-ui.card.description>
        </x-ui.card.header>
        <x-ui.card.content>
            <form method="POST" action="{{ route('profile.update') }}" class="grid gap-4 sm:grid-cols-2">
                @csrf
                @method('PATCH')
                <x-ui.field label="Nama Lengkap" for="nama_lengkap" name="nama_lengkap" required class="sm:col-span-2">
                    <x-ui.input id="nama_lengkap" name="nama_lengkap" :value="old('nama_lengkap', $user->nama_lengkap)" :invalid="$errors->has('nama_lengkap')" required />
                </x-ui.field>
                <x-ui.field label="Email" for="email" name="email" required>
                    <x-ui.input type="email" id="email" name="email" :value="old('email', $user->email)" :invalid="$errors->has('email')" autocomplete="email" required />
                </x-ui.field>
                <x-ui.field label="Telegram ID" for="telegram_id" name="telegram_id" hint="Opsional.">
                    <x-ui.input id="telegram_id" name="telegram_id" :value="old('telegram_id', $user->telegram_id)" :invalid="$errors->has('telegram_id')" placeholder="Misal: 123456789" />
                </x-ui.field>
                <div class="sm:col-span-2">
                    <x-ui.button type="submit" icon="save">Simpan Profil</x-ui.button>
                </div>
            </form>
        </x-ui.card.content>
    </x-ui.card>

    {{-- Password --}}
    <x-ui.card id="ganti-password">
        <x-ui.card.header>
            <x-ui.card.title>Ubah Kata Sandi</x-ui.card.title>
            <x-ui.card.description>Gunakan kata sandi yang kuat dan tidak dipakai di tempat lain.</x-ui.card.description>
        </x-ui.card.header>
        <x-ui.card.content>
            <form method="POST" action="{{ route('profile.password') }}" class="grid gap-4"
                  x-data="passkey({ options: @js(route('passkey.options')), verify: @js(route('passkey.confirm')), mode: 'confirm' })"
                  x-init="confirmed = @js($passkeyConfirmed)">
                @csrf
                @method('PUT')

                <div class="grid gap-2">
                    <div class="flex items-center justify-between gap-2">
                        <x-ui.label for="current_password" @class(['text-destructive' => $errors->has('current_password')])>Kata Sandi Saat Ini</x-ui.label>
                        @if ($passkeyCount > 0)
                            <button type="button" x-show="supported && !confirmed" x-on:click="run()" x-bind:disabled="busy"
                                    class="text-primary inline-flex cursor-pointer items-center gap-1 text-sm font-medium underline-offset-4 hover:underline disabled:opacity-50">
                                <span x-show="!busy" class="contents"><x-ui.icon name="fingerprint" class="size-3.5" /></span>
                                <span x-show="busy" x-cloak class="contents"><x-ui.icon name="loader-circle" class="size-3.5 animate-spin" /></span>
                                Gunakan Fingerprint/Face ID
                            </button>
                        @endif
                    </div>
                    <p x-show="confirmed" x-cloak class="text-muted-foreground flex items-center gap-2 text-sm">
                        <span class="contents"><x-ui.icon name="shield-check" class="text-primary" /></span>
                        Terverifikasi dengan fingerprint/Face ID — kata sandi saat ini tidak perlu diisi.
                    </p>
                    <div x-show="!confirmed">
                        <x-ui.input type="password" id="current_password" name="current_password" autocomplete="current-password" :invalid="$errors->has('current_password')" />
                    </div>
                    @error('current_password')<p class="text-destructive text-sm">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.field label="Kata Sandi Baru" for="password" name="password" hint="Minimal 8 karakter.">
                        <x-ui.input type="password" id="password" name="password" autocomplete="new-password" :invalid="$errors->has('password')" required />
                    </x-ui.field>
                    <x-ui.field label="Konfirmasi Kata Sandi" for="password_confirmation">
                        <x-ui.input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required />
                    </x-ui.field>
                </div>
                <div>
                    <x-ui.button type="submit" icon="key-round">Ubah Kata Sandi</x-ui.button>
                </div>
            </form>
        </x-ui.card.content>
    </x-ui.card>

    {{-- Passkeys --}}
    <x-ui.card id="passkey">
        <x-ui.card.header>
            <x-ui.card.title>Fingerprint / Face ID</x-ui.card.title>
            <x-ui.card.description>Masuk tanpa kata sandi dengan passkey. Khusus pegawai Direktorat TI &amp; TD.</x-ui.card.description>
        </x-ui.card.header>
        <x-ui.card.content class="grid gap-3">
            @if ($pegawai)
                <p class="text-sm">
                    Akun ini terhubung dengan <span class="font-medium">{{ $pegawai->nama_lengkap }}</span>@if ($pegawai->nip) (NIP {{ $pegawai->nip }})@endif di Kehadiran.
                </p>

                @forelse ($user->passkeys as $pk)
                    <div class="flex items-center gap-3 rounded-lg border p-3" x-data>
                        <span class="bg-muted flex size-9 items-center justify-center rounded-md"><x-ui.icon name="fingerprint" class="text-muted-foreground" /></span>
                        <div class="grid min-w-0 flex-1">
                            <span class="truncate text-sm font-medium">{{ $pk->name }}</span>
                            <span class="text-muted-foreground text-xs">
                                Didaftarkan {{ $pk->created_at->diffForHumans() }} ·
                                {{ $pk->last_used_at ? 'terakhir dipakai ' . $pk->last_used_at->diffForHumans() : 'belum pernah dipakai' }}
                            </span>
                        </div>
                        <form method="POST" action="{{ route('passkeys.destroy', $pk) }}" x-ref="form">
                            @csrf
                            @method('DELETE')
                            <x-ui.button type="button" variant="ghost" size="icon" aria-label="Hapus passkey" data-title="Hapus passkey {{ $pk->name }}?"
                                x-on:click="toast.confirm({ title: $el.dataset.title, description: 'Perangkat ini tidak bisa lagi dipakai untuk masuk.', confirmLabel: 'Hapus', destructive: true, onConfirm: () => $refs.form.submit() })">
                                <x-ui.icon name="trash-2" class="text-destructive" />
                            </x-ui.button>
                        </form>
                    </div>
                @empty
                    <p class="text-muted-foreground text-sm">Belum ada passkey. Daftarkan di perangkat ini supaya bisa masuk dengan fingerprint/Face ID.</p>
                @endforelse

                <div x-data="passkeyRegister({ options: @js(route('passkeys.options')), store: @js(route('passkeys.store')) })" class="grid gap-2 rounded-lg border border-dashed p-3">
                    <template x-if="supported">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-end">
                            <x-ui.field label="Nama perangkat" for="passkey_name" class="flex-1">
                                <x-ui.input id="passkey_name" x-model="name" maxlength="100" />
                            </x-ui.field>
                            <x-ui.button type="button" x-on:click="run()" x-bind:disabled="busy">
                                <span x-show="!busy" class="contents"><x-ui.icon name="plus" /></span>
                                <span x-show="busy" x-cloak class="contents"><x-ui.icon name="loader-circle" class="animate-spin" /></span>
                                Daftarkan Passkey
                            </x-ui.button>
                        </div>
                    </template>
                    <p x-show="!supported" class="text-muted-foreground text-sm">Browser ini tidak mendukung passkey.</p>
                </div>

                @if ($pegawai->passkeys->isNotEmpty())
                    <p class="text-muted-foreground text-xs">
                        Passkey Kehadiran Anda ({{ $pegawai->passkeys->pluck('name')->join(', ') }}) juga bisa dipakai lewat tautan "pakai passkey Kehadiran" di halaman login, tapi hanya di perangkat tempat passkey itu dibuat.
                    </p>
                @endif
            @else
                <x-ui.alert icon="info">
                    Fingerprint/Face ID khusus pegawai Direktorat TI &amp; TD. Akun ini belum terhubung dengan data Kehadiran; superadmin bisa menghubungkannya di Master User.
                </x-ui.alert>
            @endif
        </x-ui.card.content>
    </x-ui.card>
</div>
@endsection
