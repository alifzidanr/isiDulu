<!-- resources/views/master/user/index.blade.php -->
@extends('layouts.app')

@section('title', 'Master User - isiDulu')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-3 sm:space-y-0">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Master User</h1>
        <button onclick="openCreateModal()" 
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-auto">
            <i class="fas fa-plus mr-2"></i>Tambah User
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
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Access Level</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kampus</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $index => $user)
                    <tr>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900">
                            {{ $users->firstItem() + $index }}
                        </td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm font-medium text-gray-900">
                            {{ $user->nama_lengkap }}
                        </td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500">
                            {{ $user->email }}
                        </td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $user->status == 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($user->status) }}
                            </span>
                        </td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500">
                            @if($user->access_level == 0) Superadmin
                            @elseif($user->access_level == 1) Admin
                            @else User
                            @endif
                        </td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500">
                            {{ $user->kampus->nama_kampus ?? '-' }}
                        </td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500">
                            <div class="flex flex-col sm:flex-row sm:space-x-2 space-y-2 sm:space-y-0">
                                <button onclick="openEditModal({{ $user->id_user }}, '{{ $user->nama_lengkap }}', '{{ $user->email }}', '{{ $user->status }}', {{ $user->id_kampus ?? 'null' }}, {{ $user->id_unit ?? 'null' }}, {{ $user->access_level }})" 
                                        class="text-blue-600 hover:text-blue-900 text-left">
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </button>
                                <form action="{{ route('master.user.destroy', $user->id_user) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            onclick="return confirm('Yakin ingin menghapus user ini?')"
                                            class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash mr-1"></i>Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 sm:px-6 py-4 text-center text-gray-500">Tidak ada data user</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 sm:px-6 py-4 bg-gray-50">
            {{ $users->links() }}
        </div>
    </div>
</div>

<!-- Create Modal -->
<div id="createModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-4 sm:top-10 mx-auto p-4 sm:p-5 border w-11/12 lg:w-1/2 shadow-lg rounded-md bg-white max-h-screen overflow-y-auto">
        <div class="flex justify-between items-center pb-3">
            <h3 class="text-lg sm:text-xl font-bold text-gray-900">Tambah User</h3>
            <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
     <form action="{{ route('master.user.store') }}" method="POST" class="space-y-3 sm:space-y-4">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4">
                <div>
                    <label for="nama_lengkap" class="block text-sm/6 font-medium text-gray-900">Nama Lengkap</label>
                    <div class="mt-2">
                        <input type="text" name="nama_lengkap" id="nama_lengkap" required
                            placeholder="Masukkan nama lengkap"
                            class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                    </div>
                </div>
                <div>
                    <label for="email" class="block text-sm/6 font-medium text-gray-900">Email</label>
                    <div class="mt-2">
                        <input type="email" name="email" id="email" required
                            placeholder="user@example.com"
                            class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                    </div>
                </div>
                <div>
                    <label for="password" class="block text-sm/6 font-medium text-gray-900">Password</label>
                    <div class="mt-2">
                        <input type="password" name="password" id="password" required
                            placeholder="Masukkan password"
                            class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                    </div>
                </div>
                <div>
                    <label for="status" class="block text-sm/6 font-medium text-gray-900">Status</label>
                    <div class="mt-2">
                        <select name="status" id="status" required
                            class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                            <option value="">Pilih Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label for="id_kampus" class="block text-sm/6 font-medium text-gray-900">Kampus</label>
                    <div class="mt-2">
                        <select name="id_kampus" id="id_kampus"
                            class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                            <option value="">Pilih Kampus</option>
                            @foreach($kampuses as $kampus)
                                <option value="{{ $kampus->id_kampus }}">{{ $kampus->nama_kampus }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label for="id_unit" class="block text-sm/6 font-medium text-gray-900">Unit</label>
                    <div class="mt-2">
                        <select name="id_unit" id="id_unit"
                            class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                            <option value="">Pilih Unit</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id_unit }}">{{ $unit->nama_unit }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="lg:col-span-2">
                    <label for="access_level" class="block text-sm/6 font-medium text-gray-900">Access Level</label>
                    <div class="mt-2">
                        <select name="access_level" id="access_level" required
                            class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                            <option value="">Pilih Access Level</option>
                            <option value="0">Superadmin</option>
                            <option value="1">Admin</option>
                            <option value="2">User</option>
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
    <div class="relative top-4 sm:top-10 mx-auto p-4 sm:p-5 border w-11/12 lg:w-1/2 shadow-lg rounded-md bg-white max-h-screen overflow-y-auto">
        <div class="flex justify-between items-center pb-3">
            <h3 class="text-lg sm:text-xl font-bold text-gray-900">Edit User</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form id="editForm" method="POST" class="space-y-3 sm:space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4">
                <div>
                    <label for="edit_nama_lengkap" class="block text-sm/6 font-medium text-gray-900">Nama Lengkap</label>
                    <div class="mt-2">
                        <input type="text" name="nama_lengkap" id="edit_nama_lengkap" required
                            placeholder="Masukkan nama lengkap"
                            class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                    </div>
                </div>
                <div>
                    <label for="edit_email" class="block text-sm/6 font-medium text-gray-900">Email</label>
                    <div class="mt-2">
                        <input type="email" name="email" id="edit_email" required
                            placeholder="user@example.com"
                            class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                    </div>
                </div>
                <div>
                    <label for="edit_password" class="block text-sm/6 font-medium text-gray-900">Password (Kosongkan jika tidak ingin mengubah)</label>
                    <div class="mt-2">
                        <input type="password" name="password" id="edit_password"
                            placeholder="Masukkan password baru"
                            class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                    </div>
                </div>
                <div>
                    <label for="edit_status" class="block text-sm/6 font-medium text-gray-900">Status</label>
                    <div class="mt-2">
                        <select name="status" id="edit_status" required
                            class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                            <option value="">Pilih Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label for="edit_id_kampus" class="block text-sm/6 font-medium text-gray-900">Kampus</label>
                    <div class="mt-2">
                        <select name="id_kampus" id="edit_id_kampus"
                            class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                            <option value="">Pilih Kampus</option>
                            @foreach($kampuses as $kampus)
                                <option value="{{ $kampus->id_kampus }}">{{ $kampus->nama_kampus }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label for="edit_id_unit" class="block text-sm/6 font-medium text-gray-900">Unit</label>
                    <div class="mt-2">
                        <select name="id_unit" id="edit_id_unit"
                            class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                            <option value="">Pilih Unit</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id_unit }}">{{ $unit->nama_unit }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="lg:col-span-2">
                    <label for="edit_access_level" class="block text-sm/6 font-medium text-gray-900">Access Level</label>
                    <div class="mt-2">
                        <select name="access_level" id="edit_access_level" required
                            class="block w-full rounded-md bg-white px-3 py-2 sm:py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                            <option value="">Pilih Access Level</option>
                            <option value="0">Superadmin</option>
                            <option value="1">Admin</option>
                            <option value="2">User</option>
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

function openEditModal(id, nama, email, status, kampusId, unitId, accessLevel) {
    document.getElementById('editModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    document.getElementById('editForm').action = `/master/user/${id}`;
    document.getElementById('edit_nama_lengkap').value = nama;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_status').value = status;
    document.getElementById('edit_id_kampus').value = kampusId || '';
    document.getElementById('edit_id_unit').value = unitId || '';
    document.getElementById('edit_access_level').value = accessLevel;
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

// Modal event listeners
document.getElementById('createModal').addEventListener('click', function(e) {
    if (e.target === this) closeCreateModal();
});
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCreateModal();
        closeEditModal();
    }
});
</script>
@endpush
@endsection