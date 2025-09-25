<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Permohonan - {{ $permohonan->nama_pemohon }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { -webkit-print-color-adjust: exact; }
            .no-print { display: none; }
        }
    </style>
</head>
<body class="bg-white p-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8 border-b-2 border-gray-300 pb-4">
            <h1 class="text-2xl font-bold text-gray-900">FORM PERMOHONAN LAYANAN</h1>
            <h2 class="text-xl font-semibold text-blue-600 mt-2">isiDulu</h2>
            <p class="text-sm text-gray-600 mt-1">Sistem Manajemen Permohonan Layanan</p>
        </div>

        <!-- Print Button -->
        <div class="no-print mb-4">
            <button onclick="window.print()" 
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                <i class="fas fa-print mr-2"></i>Print
            </button>
            <button onclick="window.close()" 
                    class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 ml-2">
                <i class="fas fa-times mr-2"></i>Tutup
            </button>
        </div>

        <!-- Permohonan Details -->
        <div class="bg-white border border-gray-300 rounded-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">Informasi Pemohon</h3>
                    
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Tanggal Permohonan</label>
                            <p class="text-sm text-gray-900">{{ $permohonan->tanggal->format('d F Y') }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Nama Pemohon</label>
                            <p class="text-sm text-gray-900">{{ $permohonan->nama_pemohon }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Email</label>
                            <p class="text-sm text-gray-900">{{ $permohonan->email_pemohon ?? '-' }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Kontak</label>
                            <p class="text-sm text-gray-900">{{ $permohonan->kontak_pemohon }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Nama Pimpinan</label>
                            <p class="text-sm text-gray-900">{{ $permohonan->pimpinan_pemohon ?? '-' }}</p>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">Informasi Unit</h3>
                    
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Unit</label>
                            <p class="text-sm text-gray-900">{{ $permohonan->unit->nama_unit }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Sub Unit</label>
                            <p class="text-sm text-gray-900">{{ $permohonan->subUnit->nama_sub_unit ?? '-' }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Kampus</label>
                            <p class="text-sm text-gray-900">{{ $permohonan->unit->kampus->nama_kampus }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Status Inventaris</label>
                            <p class="text-sm text-gray-900">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $permohonan->inventaris == 'y' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $permohonan->inventaris == 'y' ? 'Inventaris' : 'Non-Inventaris' }}
                                </span>
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Status</label>
                            <p class="text-sm text-gray-900">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                    {{ $permohonan->statusText }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">Detail Permohonan</h3>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $permohonan->keluhan }}</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-xs text-gray-500">
            <p>Dokumen ini dicetak pada {{ now()->format('d F Y H:i:s') }}</p>
            <p>© {{ date('Y') }} isiDulu - Sistem Manajemen Permohonan Layanan</p>
        </div>
    </div>

    <script>
        // Auto print when opened in new window
        window.onload = function() {
            if (window.location.search.includes('auto_print=1')) {
                setTimeout(() => window.print(), 500);
            }
        };
    </script>
</body>
</html>