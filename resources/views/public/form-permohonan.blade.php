@extends('layouts.app')

@section('title', 'Form Permohonan - HelpDesk')

@php
@endphp

@section('content')
<div class="mx-auto grid max-w-2xl gap-6">
    <x-ui.button :href="route('public.index')" variant="ghost" icon="arrow-left" class="-ml-3 w-fit">Kembali ke daftar</x-ui.button>

    <x-ui.card>
        <x-ui.card.header>
            <x-ui.card.title class="text-xl">Form Permohonan</x-ui.card.title>
            <x-ui.card.description>Isi data di bawah ini. Tim TI akan menindaklanjuti permohonan Anda.</x-ui.card.description>
        </x-ui.card.header>
        <x-ui.card.content>
            <form id="publicForm" method="POST" action="{{ route('public.store') }}" class="grid gap-5 sm:grid-cols-2"
                  x-data="recaptchaForm(@js(config('services.recaptcha.site_key')), 'permohonan')" x-on:submit.prevent="submit($el)">
                @csrf
                <input type="hidden" name="recaptcha" x-ref="recaptcha">
                <x-ui.field label="Nama" for="nama_pemohon" name="nama_pemohon" required>
                    <x-ui.input id="nama_pemohon" name="nama_pemohon" :value="old('nama_pemohon')" :invalid="$errors->has('nama_pemohon')" placeholder="Nama lengkap" autocomplete="name" required x-on:input="$el.value = $el.value.replace(/[0-9]/g, '')" maxlength="100" title="Hanya huruf, tanpa angka" />
                </x-ui.field>
                <x-ui.field label="No. Handphone" for="kontak_pemohon" name="kontak_pemohon" required>
                    <x-ui.input id="kontak_pemohon" name="kontak_pemohon" :value="old('kontak_pemohon')" :invalid="$errors->has('kontak_pemohon')" placeholder="08xxxxxxxxxx" inputmode="tel" autocomplete="tel" required maxlength="20" x-on:input="$el.value = $el.value.replace(/[^0-9+\s-]/g, '')" />
                </x-ui.field>
                <x-ui.field label="Email" for="email_pemohon" name="email_pemohon" hint="Opsional.">
                    <x-ui.input type="email" id="email_pemohon" name="email_pemohon" :value="old('email_pemohon')" :invalid="$errors->has('email_pemohon')" placeholder="nama@email.com" autocomplete="email" />
                </x-ui.field>
                <x-ui.field label="Nama Pimpinan" for="pimpinan_pemohon" name="pimpinan_pemohon" hint="Opsional.">
                    <x-ui.input id="pimpinan_pemohon" name="pimpinan_pemohon" :value="old('pimpinan_pemohon')" :invalid="$errors->has('pimpinan_pemohon')" placeholder="Pimpinan unit" x-on:input="$el.value = $el.value.replace(/[0-9]/g, '')" maxlength="100" title="Hanya huruf, tanpa angka" />
                </x-ui.field>
                <x-ui.field label="Unit / Sub Unit" for="unit-picker" name="id_unit" required class="min-w-0">
                    <x-unit-picker :units="$units" :unit="old('id_unit')" :sub-unit="old('id_sub_unit')" :invalid="$errors->has('id_unit') || $errors->has('id_sub_unit')" />
                </x-ui.field>
                <x-ui.field label="Inventaris" for="inventaris" name="inventaris" required>
                    <x-ui.select id="inventaris" name="inventaris" placeholder="Pilih status" :options="['y' => 'Inventaris', 'n' => 'Non-Inventaris']" :selected="old('inventaris')" :invalid="$errors->has('inventaris')" required />
                </x-ui.field>
                <x-ui.field label="Permohonan" for="keluhan" name="keluhan" required class="sm:col-span-2">
                    <x-ui.textarea id="keluhan" name="keluhan" rows="5" :invalid="$errors->has('keluhan')" placeholder="Jelaskan kebutuhan atau kendala Anda dengan detail..." required maxlength="5000">{{ old('keluhan') }}</x-ui.textarea>
                </x-ui.field>

                <div class="flex flex-col-reverse gap-2 sm:col-span-2 sm:flex-row sm:justify-end">
                    <x-ui.button :href="route('public.index')" variant="outline">Batal</x-ui.button>
                    <x-ui.button type="submit" x-bind:disabled="sending">
                        <span x-show="!sending" class="contents"><x-ui.icon name="send" /></span>
                        <span x-show="sending" x-cloak class="contents"><x-ui.icon name="loader-circle" class="animate-spin" /></span>
                        Kirim Permohonan
                    </x-ui.button>
                </div>
            </form>
            @if (App\Rules\Recaptcha::enabled())
                <p class="text-muted-foreground mt-4 text-center text-[11px]">
                    Dilindungi reCAPTCHA; berlaku <a href="https://policies.google.com/privacy" target="_blank" rel="noreferrer" class="underline">Kebijakan Privasi</a>
                    dan <a href="https://policies.google.com/terms" target="_blank" rel="noreferrer" class="underline">Persyaratan Layanan</a> Google.
                </p>
            @endif
        </x-ui.card.content>
    </x-ui.card>
</div>
@endsection

@if (App\Rules\Recaptcha::enabled())
    @push('scripts')
        <script src="https://www.google.com/recaptcha/api.js?render={{ urlencode(config('services.recaptcha.site_key')) }}" async defer></script>
    @endpush
@endif
