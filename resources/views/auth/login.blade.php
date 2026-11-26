@extends('layouts.app')

@section('title', 'Login - HelpDesk')

@section('content')
<div class="flex justify-center pt-4 sm:pt-12">
    <div class="grid w-full max-w-sm gap-6">
        <div class="grid justify-items-center gap-2 text-center">
            <span class="bg-primary text-primary-foreground flex size-10 items-center justify-center rounded-lg">
                <x-ui.icon name="headset" class="size-5" />
            </span>
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
            </x-ui.card.content>
        </x-ui.card>

        <p class="text-muted-foreground text-center text-xs">© {{ date('Y') }} HelpDesk</p>
    </div>
</div>
@endsection
