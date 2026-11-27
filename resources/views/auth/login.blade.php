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
                <form method="POST" action="{{ route('login') }}" class="grid gap-5" x-data="recaptchaForm(@js(config('services.recaptcha.site_key')), 'login')" x-on:submit.prevent="submit($el)">
                    @csrf
                    <input type="hidden" name="recaptcha" x-ref="recaptcha">
                    <x-ui.field label="Email" for="email" name="email">
                        <x-ui.input type="email" id="email" name="email" :value="old('email')" :invalid="$errors->has('email')" placeholder="nama@email.com" autocomplete="email" autofocus required />
                    </x-ui.field>
                    <x-ui.field label="Password" for="password" name="password">
                        <x-ui.input type="password" id="password" name="password" :invalid="$errors->has('password')" autocomplete="current-password" required />
                    </x-ui.field>
                    @foreach (['recaptcha', 'throttle', 'failed'] as $key)
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

                <div x-data="{ supported: typeof window.PublicKeyCredential === 'function' }" class="mt-5 grid gap-3">
                    <div class="text-muted-foreground flex items-center gap-3 text-xs">
                        <span class="bg-border h-px flex-1"></span> atau <span class="bg-border h-px flex-1"></span>
                    </div>
                    <div x-show="supported" x-cloak x-data="passkey({ options: @js(route('passkey.options')), verify: @js(route('passkey.login')) })">
                        <x-ui.button variant="outline" class="w-full" x-on:click="run()" x-bind:disabled="busy">
                            <span x-show="!busy" class="contents"><x-ui.icon name="fingerprint" /></span>
                            <span x-show="busy" x-cloak class="contents"><x-ui.icon name="loader-circle" class="animate-spin" /></span>
                            Masuk dengan Fingerprint / Face ID
                        </x-ui.button>
                    </div>
                    <x-ui.button variant="ghost" class="w-full" :href="route('kehadiran.sso')" icon="log-in">
                        Masuk lewat Kehadiran
                    </x-ui.button>
                    <p class="text-muted-foreground text-center text-xs">
                        Khusus pegawai Direktorat TI &amp; TD. Passkey yang sama dengan Kehadiran dan HRIS;
                        di perangkat tanpa passkey, pakai "Masuk lewat Kehadiran".
                    </p>
                </div>
            </x-ui.card.content>
        </x-ui.card>

        @if (App\Rules\Recaptcha::enabled())
            <p class="text-muted-foreground text-center text-[11px] leading-relaxed">
                Situs ini dilindungi reCAPTCHA; berlaku
                <a href="https://policies.google.com/privacy" target="_blank" rel="noreferrer" class="underline underline-offset-2">Kebijakan Privasi</a> dan
                <a href="https://policies.google.com/terms" target="_blank" rel="noreferrer" class="underline underline-offset-2">Persyaratan Layanan</a> Google.
            </p>
        @endif

    </div>
</div>
@endsection

@if (App\Rules\Recaptcha::enabled())
    @push('scripts')
        <script src="https://www.google.com/recaptcha/api.js?render={{ urlencode(config('services.recaptcha.site_key')) }}" async defer></script>
    @endpush
@endif
