@extends('layouts.app')

@section('title', 'Permohonan Publik - HelpDesk')

@section('content')
<div class="grid gap-6"
     x-data="livePermohonan({ endpoint: @js(route('public.latest')), latestId: @js($permohonans->max('id_permohonan') ?? 0), enabled: @js($permohonans->onFirstPage()) })">

    <x-ui.page-header title="Daftar Permohonan" description="Pantau status permohonan layanan TI. Daftar ini diperbarui otomatis.">
        <x-slot:actions>
            <x-ui.button :href="route('public.form')" icon="plus">Buat Permohonan</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card flush>
        <div class="flex items-center justify-between border-b px-6 py-3">
            <p class="text-sm font-medium">{{ $permohonans->total() }} permohonan</p>
            <span class="flex items-center gap-2 text-xs font-medium">
                <span class="relative flex size-2">
                    <span x-show="state === 'live'" class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex size-2 rounded-full"
                          :class="{ 'bg-emerald-500': state === 'live', 'bg-destructive': state === 'error', 'bg-muted-foreground': state === 'paused' }"></span>
                </span>
                <span :class="{ 'text-emerald-600': state === 'live', 'text-destructive': state === 'error', 'text-muted-foreground': state === 'paused' }"
                      x-text="{ live: 'Live', error: 'Gagal memuat', paused: 'Halaman lama' }[state]">Live</span>
            </span>
        </div>
        <x-ui.table>
            <x-ui.table.header>
                <x-ui.table.head>Pemohon</x-ui.table.head>
                <x-ui.table.head class="min-w-64">Keterangan</x-ui.table.head>
                <x-ui.table.head>Status</x-ui.table.head>
            </x-ui.table.header>
            <x-ui.table.body x-ref="tbody">
                @forelse ($permohonans as $permohonan)
                    <x-ui.table.row data-id="{{ $permohonan->id_permohonan }}">
                        <x-ui.table.cell>
                            <div class="grid">
                                <span class="font-medium">{{ $permohonan->nama_pemohon }}</span>
                                <span class="text-muted-foreground text-xs">{{ $permohonan->unit->nama_unit ?? '-' }} · {{ $permohonan->tanggal->format('d/m/Y') }}</span>
                            </div>
                        </x-ui.table.cell>
                        <x-ui.table.cell class="text-muted-foreground">{{ Str::limit($permohonan->keluhan, 100) }}</x-ui.table.cell>
                        <x-ui.table.cell><x-status-badge :status="$permohonan->status_permohonan" /></x-ui.table.cell>
                    </x-ui.table.row>
                @empty
                    <x-ui.table.empty colspan="3" title="Belum ada permohonan" description="Permohonan yang Anda kirim akan muncul di sini." x-ref="empty" />
                @endforelse
            </x-ui.table.body>
        </x-ui.table>
        @if ($permohonans->hasPages())
            <div class="border-t px-6 py-4">{{ $permohonans->links() }}</div>
        @endif
    </x-ui.card>
</div>
@endsection
