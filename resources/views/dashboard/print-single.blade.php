<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
    <title>Print Permohonan - {{ $permohonan->nama_pemohon }}</title>
</head>
<body class="bg-muted/40 print:bg-white">
    @php
        // The petugas is whoever wrote the laporan (id_user is only a default PIC).
        $petugas = $permohonan->laporan?->creator?->nama_lengkap ?? '-';
        $informasi = [
            'Tanggal Permohonan' => $permohonan->tanggal->translatedFormat('d F Y'),
            'Nama' => $permohonan->nama_pemohon,
            'Kampus' => $permohonan->unit->kampus->nama_kampus ?? '-',
            'Unit' => $permohonan->unit->nama_unit ?? '-',
            'Sub Unit' => $permohonan->subUnit->nama_sub_unit ?? '-',
            'Status Inventaris' => $permohonan->inventaris === 'y' ? 'Inventaris' : 'Non-Inventaris',
        ];
        $ttd = \App\Support\Penandatangan::current();
    @endphp

    <div class="mx-auto max-w-3xl p-4 sm:p-8 print:max-w-none print:p-0">
        <div class="no-print mb-4 flex justify-end gap-2">
            <x-ui.button variant="outline" icon="x" onclick="window.close()">Tutup</x-ui.button>
            <x-ui.button icon="printer" onclick="window.print()">Print</x-ui.button>
        </div>

        <x-ui.card class="print:border-0 print:shadow-none">
            <x-ui.card.content class="grid gap-8">
                <div class="flex items-start justify-between gap-4 border-b pb-6">
                    <div class="flex items-center gap-3">
                        <x-app-logo compact size="size-12" />
                        <div>
                            <h1 class="text-lg font-semibold tracking-tight">Form Permohonan Layanan</h1>
                            <p class="text-muted-foreground text-sm">HelpDesk YPI Al Azhar · Sistem Manajemen Permohonan Layanan</p>
                        </div>
                    </div>
                    <x-status-badge :status="$permohonan->status_permohonan" />
                </div>

                <p class="text-sm"><span class="font-semibold">Petugas:</span> {{ $petugas }}</p>

                <div class="grid gap-3">
                    <h2 class="text-sm font-semibold">Informasi Pemohon</h2>
                    <dl class="grid gap-x-8 gap-y-3 sm:grid-cols-2 print:grid-cols-2">
                        @foreach ($informasi as $label => $value)
                            <div class="grid gap-0.5">
                                <dt class="text-muted-foreground text-xs">{{ $label }}</dt>
                                <dd class="text-sm">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                <div class="grid gap-2">
                    <h2 class="text-sm font-semibold">Detail Permohonan</h2>
                    <p class="bg-muted/50 rounded-lg border p-4 text-sm whitespace-pre-wrap">{{ $permohonan->keluhan }}</p>
                </div>

                {{-- Signed bottom right by the person in charge of isiDulu. --}}
                <div class="flex justify-end break-inside-avoid text-sm">
                    <div class="grid w-56 justify-items-center gap-1 text-center">
                        <p>Jakarta, {{ now()->translatedFormat('d F Y') }}</p>
                        <p>{{ $ttd['jabatan'] }}</p>
                        <div class="flex h-20 w-full items-center justify-center">
                            @if ($ttd['gambar'])
                                <img src="{{ $ttd['gambar'] }}" alt="Tanda tangan {{ $ttd['nama'] }}" class="max-h-20 max-w-full object-contain">
                            @endif
                        </div>
                        <p class="w-full border-t pt-1 font-semibold">{{ $ttd['nama'] }}</p>
                    </div>
                </div>

                <p class="text-muted-foreground border-t pt-4 text-center text-xs">
                    Dicetak pada {{ now()->translatedFormat('d F Y H:i') }} · &copy;{{ date('Y') }} Direktorat IT dan Transformasi Digital YPIA.
                </p>
            </x-ui.card.content>
        </x-ui.card>
    </div>

    <script>
        // Opened with ?auto_print=1: print straight away.
        window.addEventListener('load', () => {
            if (window.location.search.includes('auto_print=1')) {
                setTimeout(() => window.print(), 500);
            }
        });
    </script>
</body>
</html>
