@extends('layouts.app')

@section('title', 'Login - HelpDesk')

@section('content')
<div class="flex justify-center pt-4 sm:pt-12">
    <div class="grid w-full max-w-sm gap-6">
        <div class="grid justify-items-center gap-2 text-center">
            <x-app-logo compact size="size-16" />
            <h1 class="text-xl font-semibold tracking-tight">Masuk ke HelpDesk</h1>
            <p class="text-muted-foreground text-sm">Gunakan akun petugas untuk mengelola permohonan.</p>
        </div>

        <x-ui.card>
            <x-ui.card.content>
                <form method="POST" action="{{ route('login') }}" class="grid gap-5" x-data="{ sending: false }" x-on:submit="sending = true">
                    @csrf
                    <x-ui.field label="Email" for="email" name="email">
                        <x-ui.input type="email" id="email" name="email" :value="old('email')" :invalid="$errors->has('email')" placeholder="nama@email.com" autocomplete="email" autofocus required />
                    </x-ui.field>
                    <x-ui.field label="Password" for="password" name="password">
                        <x-ui.input type="password" id="password" name="password" :invalid="$errors->has('password')" autocomplete="current-password" required />
                    </x-ui.field>
                    @foreach (['throttle', 'failed'] as $key)
                        @error($key)
                            <x-ui.alert variant="destructive" icon="circle-x">{{ $message }}</x-ui.alert>
                        @enderror
                    @endforeach
                    <x-ui.button type="submit" class="w-full" x-bind:disabled="sending">
                        <span x-show="!sending" class="contents"><x-ui.icon name="log-in" /></span>
                        <span x-show="sending" x-cloak class="contents"><x-ui.icon name="loader-circle" class="animate-spin" /></span>
                        Masuk
                    </x-ui.button>
                </form>

                <div x-data="{ supported: typeof window.PublicKeyCredential === 'function' }" x-show="supported" x-cloak class="mt-5 grid gap-3">
                    <div class="text-muted-foreground flex items-center gap-3 text-xs">
                        <span class="bg-border h-px flex-1"></span> atau <span class="bg-border h-px flex-1"></span>
                    </div>
                    <div x-data="passkey({ options: @js(route('passkey.options')), verify: @js(route('passkey.login')) })">
                        <x-ui.button variant="outline" class="w-full" x-on:click="run()" x-bind:disabled="busy">
                            <span x-show="!busy" class="contents"><x-ui.icon name="fingerprint" /></span>
                            <span x-show="busy" x-cloak class="contents"><x-ui.icon name="loader-circle" class="animate-spin" /></span>
                            Masuk dengan Fingerprint / Face ID
                        </x-ui.button>
                    </div>
                    <p class="text-muted-foreground text-center text-xs">
                        Khusus pegawai Direktorat TI &amp; TD. Daftarkan passkey dulu di Pengaturan Profil, atau
                        <button type="button" x-data="passkey({ options: @js(route('passkey.kehadiran.options')), verify: @js(route('passkey.kehadiran.login')) })"
                                x-on:click="run()" x-bind:disabled="busy"
                                class="text-primary cursor-pointer font-medium underline-offset-4 hover:underline disabled:opacity-50">pakai passkey Kehadiran</button>.
                    </p>
                </div>
            </x-ui.card.content>
        </x-ui.card>

    </div>
</div>
@endsection
