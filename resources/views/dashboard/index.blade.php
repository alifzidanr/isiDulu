@extends('layouts.app')

@section('title', 'Dashboard - HelpDesk')
@section('page', 'Dashboard')

@section('content')
<div class="grid gap-6">
    <x-ui.page-header title="Dashboard" :description="'Selamat datang, ' . auth()->user()->nama_lengkap . '.'">
        <x-slot:actions>
            <x-ui.button :href="route('permohonan.index')" variant="outline" icon="file-text">Lihat Permohonan</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card title="Total Permohonan" :value="$stats['total_permohonan'] ?? 0" icon="file-text" hint="Semua permohonan yang masuk" />
        <x-ui.stat-card title="Menunggu" :value="$stats['permohonan_pending'] ?? 0" icon="clock" tone="text-amber-500" hint="Belum dikerjakan" />
        <x-ui.stat-card title="Dikerjakan" :value="$stats['permohonan_dikerjakan'] ?? 0" icon="play" tone="text-blue-600" hint="Sedang ditangani" />
        <x-ui.stat-card title="Selesai" :value="$stats['permohonan_selesai'] ?? 0" icon="circle-check" tone="text-emerald-600" hint="Menunggu laporan / pengesahan" />
    </div>

    <div class="grid gap-4 lg:grid-cols-5">
        <x-ui.card class="lg:col-span-2">
            <x-ui.card.header>
                <x-ui.card.title>Status Permohonan</x-ui.card.title>
                <x-ui.card.description>Sebaran permohonan per status.</x-ui.card.description>
            </x-ui.card.header>
            <x-ui.card.content>
                @if ($statusData->isEmpty())
                    <div class="text-muted-foreground flex h-[260px] items-center justify-center text-sm">Belum ada data.</div>
                @else
                    <div class="relative h-[260px]"><canvas id="statusChart" data-status='@json($statusData)'></canvas></div>
                @endif
            </x-ui.card.content>
        </x-ui.card>

        <x-ui.card class="lg:col-span-3">
            <x-ui.card.header>
                <x-ui.card.title>Permohonan Terbaru</x-ui.card.title>
                <x-ui.card.description>Lima permohonan yang paling akhir masuk.</x-ui.card.description>
                <x-slot:action>
                    <x-ui.button :href="route('permohonan.index')" variant="ghost" size="sm">Lihat semua <x-ui.icon name="chevron-right" /></x-ui.button>
                </x-slot:action>
            </x-ui.card.header>
            <x-ui.card.content>
                @forelse ($recent as $p)
                    <div class="flex items-start justify-between gap-4 border-b py-3 first:pt-0 last:border-0 last:pb-0">
                        <div class="grid min-w-0 gap-0.5">
                            <p class="truncate text-sm font-medium">{{ $p->nama_pemohon }}</p>
                            <p class="text-muted-foreground truncate text-sm">{{ Str::limit($p->keluhan, 70) }}</p>
                            <p class="text-muted-foreground text-xs">{{ $p->unit->nama_unit ?? '-' }} · {{ $p->tanggal->format('d M Y') }}</p>
                        </div>
                        <x-status-badge :status="$p->status_permohonan" />
                    </div>
                @empty
                    <p class="text-muted-foreground py-8 text-center text-sm">Belum ada permohonan.</p>
                @endforelse
            </x-ui.card.content>
        </x-ui.card>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/dashboard-chart.js')
@endpush
