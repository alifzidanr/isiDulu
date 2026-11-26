@extends('layouts.app')

@section('title', 'Print Permohonan - HelpDesk')
@section('page', 'Print Permohonan')

@section('content')
<div class="grid gap-6">
    <x-ui.page-header title="Print Permohonan" description="Permohonan yang sudah disahkan dan siap dicetak." />

    <x-ui.card flush>
        <x-ui.table>
            <x-ui.table.header>
                <x-ui.table.head>Tanggal</x-ui.table.head>
                <x-ui.table.head>Pemohon</x-ui.table.head>
                <x-ui.table.head>Unit</x-ui.table.head>
                <x-ui.table.head class="min-w-64">Keterangan</x-ui.table.head>
                <x-ui.table.head>Status</x-ui.table.head>
                <x-ui.table.head class="text-right"><span class="sr-only">Cetak</span></x-ui.table.head>
            </x-ui.table.header>
            <x-ui.table.body>
                @forelse ($permohonans as $permohonan)
                    <x-ui.table.row>
                        <x-ui.table.cell class="text-muted-foreground whitespace-nowrap tabular-nums">{{ $permohonan->tanggal->format('d/m/Y') }}</x-ui.table.cell>
                        <x-ui.table.cell>
                            <div class="grid">
                                <span class="font-medium">{{ $permohonan->nama_pemohon }}</span>
                                <span class="text-muted-foreground text-xs">{{ $permohonan->kontak_pemohon }}</span>
                            </div>
                        </x-ui.table.cell>
                        <x-ui.table.cell>
                            <div class="grid">
                                <span>{{ $permohonan->unit->nama_unit ?? '—' }}</span>
                                @if ($permohonan->subUnit)<span class="text-muted-foreground text-xs">{{ $permohonan->subUnit->nama_sub_unit }}</span>@endif
                            </div>
                        </x-ui.table.cell>
                        <x-ui.table.cell class="text-muted-foreground">{{ Str::limit($permohonan->keluhan, 80) }}</x-ui.table.cell>
                        <x-ui.table.cell><x-status-badge :status="$permohonan->status_permohonan" /></x-ui.table.cell>
                        <x-ui.table.cell class="text-right">
                            <x-ui.button :href="route('permohonan.print.single', $permohonan->id_permohonan)" target="_blank" variant="outline" size="sm" icon="printer">Print</x-ui.button>
                        </x-ui.table.cell>
                    </x-ui.table.row>
                @empty
                    <x-ui.table.empty colspan="6" title="Belum ada permohonan yang disahkan" description="Permohonan berstatus Disahkan akan muncul di sini." icon="printer" />
                @endforelse
            </x-ui.table.body>
        </x-ui.table>
        @if ($permohonans->hasPages())
            <div class="border-t px-6 py-4">{{ $permohonans->links() }}</div>
        @endif
    </x-ui.card>
</div>
@endsection
