<!-- resources/views/dashboard/permohonan.blade.php -->
@extends('layouts.app')

@section('title', 'Permohonan - isiDulu')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-3 sm:space-y-0">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Manajemen Permohonan</h1>
        <button onclick="openCreateModal()" 
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 w-full sm:w-auto">
            <i class="fas fa-plus mr-2"></i>Buat Permohonan
        </button>
    </div>
    
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
        {{ session('success') }}
    </div>
    @endif
    
    <div class="bg-white shadow rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kontak</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($permohonans as $permohonan)
                    <tr>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                            @if($permohonan->status_permohonan != 3)
                            <form action="{{ route('permohonan.status', $permohonan->id_permohonan) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()" 
                                        class="text-xs sm:text-sm border-gray-300 rounded w-full">
                                    <option value="">Action</option>
                                    @if($permohonan->status_permohonan == 0)
                                        <option value="1">Kerjakan</option>
                                    @endif
                                    @if($permohonan->status_permohonan == 1)
                                        <option value="2">Selesai</option>
                                    @endif
                                    @if($permohonan->status_permohonan == 2)
                                        <option value="4">Sahkan</option>
                                    @endif
                                    <option value="5">Batalkan</option>
                                </select>
                            </form>
                            @else
                            <span class="text-gray-400 text-xs sm:text-sm">Diarsipkan</span>
                            @endif
                        </td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900">
                            {{ $permohonan->tanggal->format('d/m/Y') }}
                        </td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900">
                            {{ $permohonan->nama_pemohon }}
                        </td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900">
                            {{ $permohonan->kontak_pemohon }}
                        </td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900">
                            <div>{{ $permohonan->unit->nama_unit }}</div>
                            @if($permohonan->subUnit)
                            <div class="text-gray-500">{{ $permohonan->subUnit->nama_sub_unit }}</div>
                            @endif
                        </td>
                        <td class="px-4 sm:px-6 py-4 text-xs sm:text-sm text-gray-900">
                            {{ Str::limit($permohonan->keluhan, 50) }}
                        </td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                @if($permohonan->status_permohonan == 0) bg-yellow-100 text-yellow-800
                                @elseif($permohonan->status_permohonan == 1) bg-blue-100 text-blue-800
                                @elseif($permohonan->status_permohonan == 2) bg-green-100 text-green-800
                                @elseif($permohonan->status_permohonan == 3) bg-gray-100 text-gray-800
                                @elseif($permohonan->status_permohonan == 4) bg-purple-100 text-purple-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ $permohonan->statusText }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 sm:px-6 py-4 text-center text-gray-500">Tidak ada permohonan</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-4 sm:px-6 py-4">
            {{ $permohonans->links() }}
        </div>
    </div>
</div>

<!-- Create Permohonan Modal -->
<div id="createModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-4 sm:top-20 mx-auto p-4 sm:p-5 border w-11/12 sm:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white max-h-screen overflow-y-auto">
        <div class="flex justify-between items-center pb-3">
            <h3 class="text-lg sm:text-xl font-bold text-gray-900">Buat Permohonan Baru</h3>
            <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form action="{{ route('permohonan.store') }}" method="POST" class="space-y-3 sm:space-y-4">
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
                        class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                        <option value="">Pilih Unit</option>
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
                        class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                        <option value="">Pilih Status</option>
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

            <div class="flex flex-col sm:flex-row sm:justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-4">
                <button type="button" onclick="closeCreateModal()" 
                        class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 text-center sm:text-left">
                    Batal
                </button>
                <button type="submit" 
                        class="rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                    Submit Permohonan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openCreateModal() {
    document.getElementById('createModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

// Close modal when clicking outside
document.getElementById('createModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCreateModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCreateModal();
    }
});
</script>
@endpush
@endsection