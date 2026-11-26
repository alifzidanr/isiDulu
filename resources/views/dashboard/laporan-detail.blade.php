@extends('layouts.app')

@section('title', 'Detail Laporan - HelpDesk')
@section('page', 'Permohonan / Laporan')

@php
    $laporan = $permohonan->laporan;
    $info = array_filter([
        'Tanggal Permohonan' => $permohonan->tanggal->translatedFormat('d F Y'),
        'Nama Pemohon' => $permohonan->nama_pemohon,
        'Kontak' => $permohonan->kontak_pemohon,
        'Email' => $permohonan->email_pemohon,
        'Pimpinan' => $permohonan->pimpinan_pemohon,
        'Unit' => trim(($permohonan->unit->nama_unit ?? '-') . ($permohonan->subUnit ? ' / ' . $permohonan->subUnit->nama_sub_unit : '')),
        'Inventaris' => $permohonan->inventaris === 'y' ? 'Inventaris' : 'Non-Inventaris',
    ]);
@endphp

@section('content')
<div class="mx-auto grid max-w-4xl gap-6">
    <div class="no-print flex flex-wrap items-center justify-between gap-2">
        <x-ui.button :href="route('permohonan.index')" variant="ghost" icon="arrow-left" class="-ml-3">Kembali ke Permohonan</x-ui.button>
        <x-ui.button variant="outline" icon="printer" onclick="window.print()">Cetak Laporan</x-ui.button>
    </div>

    <x-ui.page-header title="Detail Laporan" :description="'Laporan untuk permohonan #' . $permohonan->id_permohonan . '.'">
        <x-slot:actions><x-status-badge :status="$permohonan->status_permohonan" /></x-slot:actions>
    </x-ui.page-header>

    <x-ui.card>
        <x-ui.card.header>
            <x-ui.card.title>Informasi Permohonan</x-ui.card.title>
        </x-ui.card.header>
        <x-ui.card.content class="grid gap-6">
            <dl class="grid gap-x-6 gap-y-4 sm:grid-cols-2">
                @foreach ($info as $label => $value)
                    <div class="grid gap-1">
                        <dt class="text-muted-foreground text-sm">{{ $label }}</dt>
                        <dd class="text-sm font-medium">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
            <x-ui.separator />
            <div class="grid gap-1">
                <p class="text-muted-foreground text-sm">Keterangan Permohonan</p>
                <p class="text-sm whitespace-pre-line">{{ $permohonan->keluhan }}</p>
            </div>
        </x-ui.card.content>
    </x-ui.card>

    <x-ui.card>
        <x-ui.card.header>
            <x-ui.card.title>Detail Pekerjaan</x-ui.card.title>
            <x-ui.card.description>Dicatat oleh petugas saat permohonan selesai.</x-ui.card.description>
        </x-ui.card.header>
        <x-ui.card.content class="grid gap-4">
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ([
                    ['Jenis Perangkat', $laporan->jenisPerangkat->nama_perangkat ?? '-', 'laptop'],
                    ['Detail Perangkat', $laporan->detail_perangkat, 'info'],
                    ['Jenis Perawatan', $laporan->jenisPerawatan->nama_perawatan ?? '-', 'wrench'],
                    ['Detail Perawatan', $laporan->detailPerawatan->nama_detail_perawatan ?? '-', 'list-checks'],
                ] as [$label, $value, $icon])
                    <div class="bg-muted/50 flex items-start gap-3 rounded-lg border p-4">
                        <x-ui.icon :name="$icon" class="text-muted-foreground mt-0.5" />
                        <div class="grid gap-0.5">
                            <p class="text-muted-foreground text-xs">{{ $label }}</p>
                            <p class="text-sm font-medium">{{ $value }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="grid gap-1 rounded-lg border p-4">
                <p class="text-sm font-medium">Uraian Pekerjaan</p>
                <p class="text-muted-foreground text-sm whitespace-pre-line">{{ $laporan->uraian_pekerjaan }}</p>
            </div>

            @if ($laporan->catatan)
                <x-ui.alert icon="sticky-note" title="Catatan">
                    <span class="whitespace-pre-line">{{ $laporan->catatan }}</span>
                </x-ui.alert>
            @endif
        </x-ui.card.content>
        <x-ui.card.footer class="border-t pt-6">
            <div class="flex items-center gap-3">
                <x-ui.avatar :name="$laporan->creator->nama_lengkap ?? '?'" size="size-10" />
                <div class="grid gap-0.5">
                    <p class="text-sm font-medium">{{ $laporan->creator->nama_lengkap ?? 'Petugas' }}</p>
                    <p class="text-muted-foreground text-xs">
                        {{ $laporan->creator->email ?? '' }} · Dibuat {{ $laporan->created_at->translatedFormat('d F Y, H:i') }}
                    </p>
                </div>
            </div>
        </x-ui.card.footer>
    </x-ui.card>
</div>
@endsection
