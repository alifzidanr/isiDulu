<!-- resources/views/dashboard/laporan-detail.blade.php -->
@extends('layouts.app')

@section('title', 'Detail Laporan - HelpDesk')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Back Button -->
    <div>
        <a href="{{ route('permohonan.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>
            Kembali ke Permohonan
        </a>
    </div>

    <!-- Laporan Header -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
            <h1 class="text-2xl font-bold text-white">Detail Laporan</h1>
            <p class="text-blue-100 text-sm mt-1">Laporan Permohonan #{{ $permohonan->id_permohonan }}</p>
        </div>

        <div class="p-6">
            <!-- Permohonan Info -->
            <div class="mb-6 pb-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Permohonan</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Tanggal Permohonan</p>
                        <p class="text-base font-medium text-gray-900">{{ $permohonan->tanggal->format('d F Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full 
                            @if($permohonan->status_permohonan == 2) bg-green-100 text-green-800
                            @elseif($permohonan->status_permohonan == 4) bg-purple-100 text-purple-800
                            @endif">
                            {{ $permohonan->statusText }}
                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Nama Pemohon</p>
                        <p class="text-base font-medium text-gray-900">{{ $permohonan->nama_pemohon }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Kontak</p>
                        <p class="text-base font-medium text-gray-900">{{ $permohonan->kontak_pemohon }}</p>
                    </div>
                    @if($permohonan->email_pemohon)
                    <div>
                        <p class="text-sm text-gray-500">Email</p>
                        <p class="text-base font-medium text-gray-900">{{ $permohonan->email_pemohon }}</p>
                    </div>
                    @endif
                    @if($permohonan->pimpinan_pemohon)
                    <div>
                        <p class="text-sm text-gray-500">Pimpinan</p>
                        <p class="text-base font-medium text-gray-900">{{ $permohonan->pimpinan_pemohon }}</p>
                    </div>
                    @endif
                    <div>
                        <p class="text-sm text-gray-500">Unit</p>
                        <p class="text-base font-medium text-gray-900">{{ $permohonan->unit->nama_unit }}</p>
                        @if($permohonan->subUnit)
                        <p class="text-sm text-gray-600">{{ $permohonan->subUnit->nama_sub_unit }}</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Inventaris</p>
                        <p class="text-base font-medium text-gray-900">
                            {{ $permohonan->inventaris == 'y' ? 'Inventaris' : 'Non-Inventaris' }}
                        </p>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-sm text-gray-500">Keterangan Permohonan</p>
                    <p class="text-base text-gray-900 mt-1">{{ $permohonan->keluhan }}</p>
                </div>
            </div>

            <!-- Laporan Info -->
            <div class="mb-6 pb-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Detail Laporan Pekerjaan</h2>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-500 mb-1">Jenis Perangkat</p>
                            <p class="text-base font-medium text-gray-900">{{ $permohonan->laporan->jenisPerangkat->nama_perangkat }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-500 mb-1">Detail Perangkat</p>
                            <p class="text-base font-medium text-gray-900">{{ $permohonan->laporan->detail_perangkat }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-500 mb-1">Jenis Perawatan</p>
                            <p class="text-base font-medium text-gray-900">{{ $permohonan->laporan->jenisPerawatan->nama_perawatan }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-500 mb-1">Detail Perawatan</p>
                            <p class="text-base font-medium text-gray-900">{{ $permohonan->laporan->detailPerawatan->nama_detail_perawatan }}</p>
                        </div>
                    </div>

                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-700 font-medium mb-2">Uraian Pekerjaan</p>
                        <p class="text-base text-gray-900 whitespace-pre-line">{{ $permohonan->laporan->uraian_pekerjaan }}</p>
                    </div>

                    @if($permohonan->laporan->catatan)
                    <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                        <p class="text-sm text-gray-700 font-medium mb-2">
                            <i class="fas fa-sticky-note text-yellow-600 mr-1"></i>
                            Catatan
                        </p>
                        <p class="text-base text-gray-900 whitespace-pre-line">{{ $permohonan->laporan->catatan }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Petugas Info -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Yang Menyelesaikan</h2>
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-4 rounded-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-white text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-base font-semibold text-gray-900">{{ $permohonan->laporan->creator->nama_user }}</p>
                            <p class="text-sm text-gray-600">{{ $permohonan->laporan->creator->email }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                <i class="far fa-calendar mr-1"></i>
                                Dibuat: {{ $permohonan->laporan->created_at->format('d F Y, H:i') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex flex-col sm:flex-row gap-3">
                <button onclick="window.print()" 
                        class="flex-1 sm:flex-none inline-flex justify-center items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                    <i class="fas fa-print mr-2"></i>
                    Cetak Laporan
                </button>
                <a href="{{ route('permohonan.index') }}" 
                   class="flex-1 sm:flex-none inline-flex justify-center items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
@media print {
    body * {
        visibility: hidden;
    }
    .max-w-4xl, .max-w-4xl * {
        visibility: visible;
    }
    .max-w-4xl {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    button, a[href*="permohonan"] {
        display: none !important;
    }
    .bg-gradient-to-r {
        background: #2563eb !important;
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }
}
</style>
@endpush
@endsection