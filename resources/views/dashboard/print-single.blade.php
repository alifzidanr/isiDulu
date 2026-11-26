<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Permohonan - {{ $permohonan->nama_pemohon }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-muted/40 print:bg-white">
    @php
        $sections = [
            'Informasi Pemohon' => [
                'Tanggal Permohonan' => $permohonan->tanggal->translatedFormat('d F Y'),
                'Nama Pemohon' => $permohonan->nama_pemohon,
                'Email' => $permohonan->email_pemohon ?: '-',
                'Kontak' => $permohonan->kontak_pemohon,
                'Nama Pimpinan' => $permohonan->pimpinan_pemohon ?: '-',
            ],
            'Informasi Unit' => [
                'Kampus' => $permohonan->unit->kampus->nama_kampus ?? '-',
                'Unit' => $permohonan->unit->nama_unit ?? '-',
                'Sub Unit' => $permohonan->subUnit->nama_sub_unit ?? '-',
                'Status Inventaris' => $permohonan->inventaris === 'y' ? 'Inventaris' : 'Non-Inventaris',
            ],
        ];
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
                        <span class="bg-primary text-primary-foreground flex size-10 items-center justify-center rounded-lg">
                            <x-ui.icon name="headset" class="size-5" />
                        </span>
                        <div>
                            <h1 class="text-lg font-semibold tracking-tight">Form Permohonan Layanan</h1>
                            <p class="text-muted-foreground text-sm">HelpDesk · Sistem Manajemen Permohonan Layanan</p>
                        </div>
                    </div>
                    <div class="grid justify-items-end gap-1 text-right">
                        <span class="text-muted-foreground text-xs">No. #{{ $permohonan->id_permohonan }}</span>
                        <x-status-badge :status="$permohonan->status_permohonan" />
                    </div>
                </div>

                <div class="grid gap-8 sm:grid-cols-2 print:grid-cols-2">
                    @foreach ($sections as $heading => $rows)
                        <div class="grid content-start gap-3">
                            <h2 class="text-sm font-semibold">{{ $heading }}</h2>
                            <dl class="grid gap-3">
                                @foreach ($rows as $label => $value)
                                    <div class="grid gap-0.5">
                                        <dt class="text-muted-foreground text-xs">{{ $label }}</dt>
                                        <dd class="text-sm">{{ $value }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    @endforeach
                </div>

                <div class="grid gap-2">
                    <h2 class="text-sm font-semibold">Detail Permohonan</h2>
                    <p class="bg-muted/50 rounded-lg border p-4 text-sm whitespace-pre-wrap">{{ $permohonan->keluhan }}</p>
                </div>

                <p class="text-muted-foreground border-t pt-4 text-center text-xs">
                    Dicetak pada {{ now()->translatedFormat('d F Y H:i') }} · © {{ date('Y') }} HelpDesk
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
