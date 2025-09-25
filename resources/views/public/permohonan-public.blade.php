<!-- resources/views/public/permohonan-public.blade.php -->
@extends('layouts.app')

@section('title', 'Permohonan Publik - isiDulu')

@section('content')
<div class="max-w-7xl mx-auto p-4 sm:p-6">
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-3 sm:space-y-0">
            <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Daftar Permohonan</h2>
            <a href="{{ route('public.form') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-center sm:text-left">
                <i class="fas fa-plus mr-2"></i>Buat Permohonan
            </a>
        </div>
        
        @if(session('success'))
        <div class="mx-4 sm:mx-6 mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
        @endif

        <!-- Table View with Mobile Scroll -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pemohon</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($permohonans as $permohonan)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $permohonan->tanggal->format('d/m/Y') }}</div>
                            <div class="text-sm font-medium text-gray-900">{{ $permohonan->nama_pemohon }}</div>
                            <div class="text-sm text-gray-500">{{ $permohonan->unit->nama_unit }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ Str::limit($permohonan->keluhan, 100) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                @if($permohonan->status_permohonan == 0) bg-yellow-100 text-yellow-800
                                @elseif($permohonan->status_permohonan == 1) bg-blue-100 text-blue-800
                                @elseif($permohonan->status_permohonan == 2) bg-green-100 text-green-800
                                @elseif($permohonan->status_permohonan == 4) bg-purple-100 text-purple-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ $permohonan->statusText }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">Tidak ada permohonan</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-4 sm:px-6 py-4 border-t border-gray-200">
            {{ $permohonans->links() }}
        </div>
    </div>
</div>
@endsection