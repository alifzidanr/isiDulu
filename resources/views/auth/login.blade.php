<!-- resources/views/auth/login.blade.php -->
@extends('layouts.app')

@section('title', 'Login - isiDulu')

@section('content')
<div class="min-h-screen flex items-start justify-center bg-gray-50 pt-8 sm:pt-16 md:pt-24 pb-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-lg shadow-md p-6 sm:p-8">
            <div class="text-center mb-6 sm:mb-8">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">
                    Login
                </h2>
                <p class="text-sm text-gray-600">
                    Masuk ke admin panel isiDulu
                </p>
            </div>
            
            <form class="space-y-4 sm:space-y-6" action="{{ route('login') }}" method="POST">
                @csrf
                
                <div>
                    <label for="email" class="block text-sm/6 font-medium text-gray-900">Email address</label>
                    <div class="mt-2">
                        <input id="email" name="email" type="email" autocomplete="email" required 
                               placeholder="you@example.com"
                               value="{{ old('email') }}"
                               class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6 @error('email') outline-red-500 focus:outline-red-500 @enderror">
                    </div>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm/6 font-medium text-gray-900">Password</label>
                    <div class="mt-2">
                        <input id="password" name="password" type="password" autocomplete="current-password" required 
                               placeholder="Enter your password"
                               class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6 @error('password') outline-red-500 focus:outline-red-500 @enderror">
                    </div>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Display any other errors that aren't field-specific -->
                @if ($errors->has('throttle') || $errors->has('failed'))
                    <div class="rounded-md bg-red-50 p-4">
                        <div class="text-sm text-red-800">
                            @if($errors->has('throttle'))
                                <p>{{ $errors->first('throttle') }}</p>
                            @endif
                            @if($errors->has('failed'))
                                <p>{{ $errors->first('failed') }}</p>
                            @endif
                        </div>
                    </div>
                @endif

                <div>
                    <button type="submit" 
                            class="group relative w-full flex justify-center rounded-md bg-blue-600 px-3 py-2.5 sm:py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-colors">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-sign-in-alt text-blue-500 group-hover:text-blue-400 text-sm"></i>
                        </span>
                        Sign in
                    </button>
                </div>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-xs text-gray-500">
                    © {{ date('Y') }} isiDulu. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection