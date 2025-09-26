<!-- resources/views/public/form-permohonan.blade.php -->
@extends('layouts.app')

@section('title', 'Form Permohonan - isiDulu')

@section('content')
<div class="max-w-2xl mx-auto p-4 sm:p-6">
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Form Permohonan</h2>
        </div>
        
        <form action="{{ route('public.store') }}" method="POST" class="p-4 sm:p-6 space-y-4 sm:space-y-6">
            @csrf
            
            <div>
                <label for="nama_pemohon" class="block text-sm/6 font-medium text-gray-900">Nama</label>
                <div class="mt-2">
                    <input type="text" name="nama_pemohon" id="nama_pemohon" required
                        placeholder="Masukkan nama lengkap"
                        class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6" 
                        value="{{ old('nama_pemohon') }}">
                </div>
                @error('nama_pemohon')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email_pemohon" class="block text-sm/6 font-medium text-gray-900">Email</label>
                <div class="mt-2">
                    <input type="email" name="email_pemohon" id="email_pemohon"
                        placeholder="you@example.com"
                        class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6" 
                        value="{{ old('email_pemohon') }}">
                </div>
                @error('email_pemohon')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="kontak_pemohon" class="block text-sm/6 font-medium text-gray-900">No Handphone</label>
                <div class="mt-2">
                    <input type="text" name="kontak_pemohon" id="kontak_pemohon" required
                        placeholder="08xxxxxxxxxx"
                        class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6" 
                        value="{{ old('kontak_pemohon') }}">
                </div>
                @error('kontak_pemohon')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="pimpinan_pemohon" class="block text-sm/6 font-medium text-gray-900">Nama Pimpinan</label>
                <div class="mt-2">
                    <input type="text" name="pimpinan_pemohon" id="pimpinan_pemohon"
                        placeholder="Nama pimpinan unit"
                        class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6" 
                        value="{{ old('pimpinan_pemohon') }}">
                </div>
                @error('pimpinan_pemohon')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="id_unit" class="block text-sm/6 font-medium text-gray-900">Unit</label>
                <div class="mt-2">
                    <select name="id_unit" id="id_unit" required
                        class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236b7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[length:1.25rem] bg-[right_0.5rem_center] bg-no-repeat pr-10">
                        <option value="" class="text-gray-400">Pilih Unit</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id_unit }}" {{ old('id_unit') == $unit->id_unit ? 'selected' : '' }}>
                                {{ $unit->nama_unit }} - {{ $unit->kampus->nama_kampus }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('id_unit')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="inventaris" class="block text-sm/6 font-medium text-gray-900">Inventaris</label>
                <div class="mt-2">
                    <select name="inventaris" id="inventaris" required
                        class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236b7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[length:1.25rem] bg-[right_0.5rem_center] bg-no-repeat pr-10">
                        <option value="" class="text-gray-400">Pilih Status</option>
                        <option value="y" {{ old('inventaris') == 'y' ? 'selected' : '' }}>Inventaris</option>
                        <option value="n" {{ old('inventaris') == 'n' ? 'selected' : '' }}>Non-Inventaris</option>
                    </select>
                </div>
                @error('inventaris')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="keluhan" class="block text-sm/6 font-medium text-gray-900">Permohonan</label>
                <div class="mt-2">
                    <textarea name="keluhan" id="keluhan" rows="4" required
                        placeholder="Deskripsikan permohonan Anda dengan detail..."
                        class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">{{ old('keluhan') }}</textarea>
                </div>
                @error('keluhan')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col sm:flex-row sm:justify-end space-y-3 sm:space-y-0 sm:space-x-3">
                <a href="{{ route('public.index') }}" 
                   class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 text-center sm:text-left">
                    Batal
                </a>
                <button type="submit" 
                        class="rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                    Submit Permohonan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection