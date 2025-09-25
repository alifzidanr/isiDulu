<!-- resources/views/master/perangkat-terdaftar/index.blade.php -->
@extends('layouts.app')

@section('title', 'Master Perangkat Terdaftar - isiDulu')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-3 sm:space-y-0">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Master Perangkat Terdaftar</h1>
        <button onclick="openCreateModal()" 
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-auto">
            <i class="fas fa-plus mr-2"></i>Tambah Perangkat
        </button>
    </div>
    
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
        {{ session('success') }}
    </div>
    @endif
    
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Perangkat</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengguna</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Inventaris</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($perangkatTerdaftars as $index => $perangkat)
                    <tr>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900">
                            {{ $perangkatTerdaftars->firstItem() + $index }}
                        </td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm font-medium text-gray-900">
                            {{ $perangkat->nama_perangkat_terdaftar }}
                        </td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500">
                            {{ $perangkat->jenisPerangkat->nama_perangkat ?? '-' }}
                        </td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500">
                            {{ $perangkat->pengguna ?? '-' }}
                        </td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $perangkat->inventaris == 'y' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $perangkat->inventaris == 'y' ? 'Inventaris' : 'Non-Inventaris' }}
                            </span>
                        </td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500">
                            <div class="flex flex-col sm:flex-row sm:space-x-2 space-y-2 sm:space-y-0">
                                <button onclick="openEditModal({{ $perangkat->id_perangkat_terdaftar }}, '{{ $perangkat->nama_perangkat_terdaftar }}', {{ $perangkat->id_jenis_perangkat }}, '{{ $perangkat->pengguna }}', '{{ $perangkat->inventaris }}')" 
                                        class="text-blue-600 hover:text-blue-900 text-left">
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </button>
                                <form action="{{ route('master.perangkat-terdaftar.destroy', $perangkat->id_perangkat_terdaftar) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            onclick="return confirm('Yakin ingin menghapus perangkat ini?')"
                                            class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash mr-1"></i>Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 sm:px-6 py-4 text-center text-gray-500">Tidak ada data perangkat terdaftar</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 sm:px-6 py-4 bg-gray-50">
            {{ $perangkatTerdaftars->links() }}
        </div>
    </div>
</div>

<!-- Create Modal -->
<div id="createModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-4 sm:top-20 mx-auto p-4 sm:p-5 border w-11/12 lg:w-1/2 shadow-lg rounded-md bg-white max-h-screen overflow-y-auto">
        <div class="flex justify-between items-center pb-3">
            <h3 class="text-lg sm:text-xl font-bold text-gray-900">Tambah Perangkat Terdaftar</h3>
            <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form action="{{ route('master.perangkat-terdaftar.store') }}" method="POST" class="space-y-3 sm:space-y-4">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4">
                <div>
                    <label for="nama_perangkat_terdaftar" class="block text-sm/6 font-medium text-gray-900">Nama Perangkat</label>
                    <div class="mt-2">
                        <input type="text" name="nama_perangkat_terdaftar" id="nama_perangkat_terdaftar" required
                            placeholder="Masukkan nama perangkat"
                            class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                    </div>
                </div>
                <div>
                    <label for="id_jenis_perangkat" class="block text-sm/6 font-medium text-gray-900">Jenis Perangkat</label>
                    <div class="mt-2">
                        <select name="id_jenis_perangkat" id="id_jenis_perangkat" required
                            class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                            <option value="">Pilih Jenis Perangkat</option>
                            @foreach($jenisPerangkats as $jenisPerangkat)
                                <option value="{{ $jenisPerangkat->id_jenis_perangkat }}">{{ $jenisPerangkat->nama_perangkat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label for="pengguna" class="block text-sm/6 font-medium text-gray-900">Pengguna</label>
                    <div class="mt-2">
                        <input type="text" name="pengguna" id="pengguna"
                            placeholder="Masukkan nama pengguna"
                            class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                    </div>
                </div>
                <div>
                    <label for="inventaris" class="block text-sm/6 font-medium text-gray-900">Status Inventaris</label>
                    <div class="mt-2">
                        <select name="inventaris" id="inventaris" required
                            class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                            <option value="">Pilih Status</option>
                            <option value="y">Inventaris</option>
                            <option value="n">Non-Inventaris</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-4">
                <button type="button" onclick="closeCreateModal()" 
                        class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 text-center sm:text-left">
                    Batal
                </button>
                <button type="submit" 
                        class="rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-4 sm:top-20 mx-auto p-4 sm:p-5 border w-11/12 lg:w-1/2 shadow-lg rounded-md bg-white max-h-screen overflow-y-auto">
        <div class="flex justify-between items-center pb-3">
            <h3 class="text-lg sm:text-xl font-bold text-gray-900">Edit Perangkat Terdaftar</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form id="editForm" method="POST" class="space-y-3 sm:space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4">
                <div>
                    <label for="edit_nama_perangkat_terdaftar" class="block text-sm/6 font-medium text-gray-900">Nama Perangkat</label>
                    <div class="mt-2">
                        <input type="text" name="nama_perangkat_terdaftar" id="edit_nama_perangkat_terdaftar" required
                            placeholder="Masukkan nama perangkat"
                            class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                    </div>
                </div>
                <div>
                    <label for="edit_id_jenis_perangkat" class="block text-sm/6 font-medium text-gray-900">Jenis Perangkat</label>
                    <div class="mt-2">
                        <select name="id_jenis_perangkat" id="edit_id_jenis_perangkat" required
                            class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                            <option value="">Pilih Jenis Perangkat</option>
                            @foreach($jenisPerangkats as $jenisPerangkat)
                                <option value="{{ $jenisPerangkat->id_jenis_perangkat }}">{{ $jenisPerangkat->nama_perangkat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label for="edit_pengguna" class="block text-sm/6 font-medium text-gray-900">Pengguna</label>
                    <div class="mt-2">
                        <input type="text" name="pengguna" id="edit_pengguna"
                            placeholder="Masukkan nama pengguna"
                            class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                    </div>
                </div>
                <div>
                    <label for="edit_inventaris" class="block text-sm/6 font-medium text-gray-900">Status Inventaris</label>
                    <div class="mt-2">
                        <select name="inventaris" id="edit_inventaris" required
                            class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                            <option value="">Pilih Status</option>
                            <option value="y">Inventaris</option>
                            <option value="n">Non-Inventaris</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-4">
                <button type="button" onclick="closeEditModal()" 
                        class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 text-center sm:text-left">
                    Batal
                </button>
                <button type="submit" 
                      class="rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
                    Update
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
    document.querySelector('#createModal form').reset();
}

function openEditModal(id, nama, jenisId, pengguna, inventaris) {
    document.getElementById('editModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    document.getElementById('editForm').action = `/master/perangkat-terdaftar/${id}`;
    document.getElementById('edit_nama_perangkat_terdaftar').value = nama;
    document.getElementById('edit_id_jenis_perangkat').value = jenisId;
    document.getElementById('edit_pengguna').value = pengguna || '';
    document.getElementById('edit_inventaris').value = inventaris;
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

// Modal event listeners
document.getElementById('createModal').addEventListener('click', function(e) { if (e.target === this) closeCreateModal(); });
document.getElementById('editModal').addEventListener('click', function(e) { if (e.target === this) closeEditModal(); });
document.addEventListener('keydown', function(e) { if (e.key === 'Escape') { closeCreateModal(); closeEditModal(); } });
</script>
@endpush
@endsection