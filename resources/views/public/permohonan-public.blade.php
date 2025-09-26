<!-- resources/views/public/permohonan-public.blade.php -->
@extends('layouts.app')

@section('title', 'Permohonan Publik - isiDulu')

@section('content')
<div class="max-w-7xl mx-auto p-4 sm:p-6">
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-3 sm:space-y-0">
            <div class="flex items-center space-x-3">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Daftar Permohonan</h2>
                <div id="status-indicator" class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-xs text-green-600">Live</span>
                </div>
            </div>
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
                <tbody id="permohonan-tbody" class="bg-white divide-y divide-gray-200">
                    @forelse($permohonans as $permohonan)
                    <tr data-id="{{ $permohonan->id_permohonan }}">
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
                    <tr id="empty-row">
                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">Tidak ada permohonan</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($permohonans->hasPages())
        <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
            <!-- Mobile Pagination -->
            <div class="flex flex-1 justify-between sm:hidden">
                @if($permohonans->onFirstPage())
                    <span class="relative inline-flex items-center rounded-md border border-gray-300 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-400 cursor-not-allowed">Previous</span>
                @else
                    <a href="{{ $permohonans->previousPageUrl() }}" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</a>
                @endif
                
                @if($permohonans->hasMorePages())
                    <a href="{{ $permohonans->nextPageUrl() }}" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Next</a>
                @else
                    <span class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-400 cursor-not-allowed">Next</span>
                @endif
            </div>
            
            <!-- Desktop Pagination -->
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing
                        <span class="font-medium">{{ $permohonans->firstItem() ?? 0 }}</span>
                        to
                        <span class="font-medium">{{ $permohonans->lastItem() ?? 0 }}</span>
                        of
                        <span class="font-medium">{{ $permohonans->total() }}</span>
                        results
                    </p>
                </div>
                <div>
                    <nav aria-label="Pagination" class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                        <!-- Previous Button -->
                        @if($permohonans->onFirstPage())
                            <span class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-300 ring-1 ring-inset ring-gray-300 cursor-not-allowed">
                                <span class="sr-only">Previous</span>
                                <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="size-5">
                                    <path d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" fill-rule="evenodd" />
                                </svg>
                            </span>
                        @else
                            <a href="{{ $permohonans->previousPageUrl() }}" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                <span class="sr-only">Previous</span>
                                <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="size-5">
                                    <path d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" fill-rule="evenodd" />
                                </svg>
                            </a>
                        @endif
                        
                        <!-- Page Numbers -->
                        @foreach($permohonans->getUrlRange(1, $permohonans->lastPage()) as $page => $url)
                            @if($page == $permohonans->currentPage())
                                <a href="{{ $url }}" aria-current="page" class="relative z-10 inline-flex items-center bg-blue-600 px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">{{ $page }}</a>
                            @elseif($page == 1 || $page == $permohonans->lastPage() || abs($page - $permohonans->currentPage()) <= 2)
                                <a href="{{ $url }}" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 @if(abs($page - $permohonans->currentPage()) > 1) hidden md:inline-flex @endif">{{ $page }}</a>
                            @elseif(abs($page - $permohonans->currentPage()) == 3)
                                <span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 focus:outline-offset-0">...</span>
                            @endif
                        @endforeach
                        
                        <!-- Next Button -->
                        @if($permohonans->hasMorePages())
                            <a href="{{ $permohonans->nextPageUrl() }}" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                <span class="sr-only">Next</span>
                                <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="size-5">
                                    <path d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" fill-rule="evenodd" />
                                </svg>
                            </a>
                        @else
                            <span class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-300 ring-1 ring-inset ring-gray-300 cursor-not-allowed">
                                <span class="sr-only">Next</span>
                                <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="size-5">
                                    <path d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" fill-rule="evenodd" />
                                </svg>
                            </span>
                        @endif
                    </nav>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
    let latestId = {{ $permohonans->first()?->id_permohonan ?? 0 }};
    let pollingInterval = null;
    let isFirstPage = {{ $permohonans->currentPage() == 1 ? 'true' : 'false' }};

    function getStatusBadgeClass(status) {
        const classes = {
            0: 'bg-yellow-100 text-yellow-800',
            1: 'bg-blue-100 text-blue-800',
            2: 'bg-green-100 text-green-800',
            4: 'bg-purple-100 text-purple-800',
            5: 'bg-red-100 text-red-800'
        };
        return classes[status] || 'bg-gray-100 text-gray-800';
    }

    function getStatusText(status) {
        const texts = {
            0: 'Permohonan',
            1: 'Dikerjakan',
            2: 'Selesai',
            3: 'Diarsipkan',
            4: 'Disahkan',
            5: 'Dibatalkan'
        };
        return texts[status] || 'Unknown';
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function truncateText(text, limit = 100) {
        return text.length > limit ? text.substring(0, limit) + '...' : text;
    }

    function addNewPermohonan(permohonan) {
        const tbody = document.getElementById('permohonan-tbody');
        const emptyRow = document.getElementById('empty-row');
        
        // Remove empty row if exists
        if (emptyRow) {
            emptyRow.remove();
        }
        
        // Check if row already exists
        if (document.querySelector(`tr[data-id="${permohonan.id_permohonan}"]`)) {
            return;
        }
        
        const row = document.createElement('tr');
        row.setAttribute('data-id', permohonan.id_permohonan);
        row.className = 'animate-slide-in bg-blue-50';
        
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${formatDate(permohonan.tanggal)}</div>
                <div class="text-sm font-medium text-gray-900">${permohonan.nama_pemohon}</div>
                <div class="text-sm text-gray-500">${permohonan.unit.nama_unit}</div>
            </td>
            <td class="px-6 py-4">
                <div class="text-sm text-gray-900">${truncateText(permohonan.keluhan)}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusBadgeClass(permohonan.status_permohonan)}">
                    ${getStatusText(permohonan.status_permohonan)}
                </span>
            </td>
        `;
        
        tbody.insertBefore(row, tbody.firstChild);
        
        // Remove highlight after animation
        setTimeout(() => {
            row.classList.remove('bg-blue-50');
        }, 2000);
    }

    function checkNewPermohonan() {
        // Only poll if on first page
        if (!isFirstPage) {
            return;
        }

        fetch(`{{ route('public.latest') }}?last_id=${latestId}`)
            .then(response => response.json())
            .then(data => {
                if (data.count > 0) {
                    // Update latest ID
                    latestId = data.latest_id;
                    
                    // Add new permohonan to the table automatically
                    data.permohonans.forEach(permohonan => {
                        addNewPermohonan(permohonan);
                    });
                    
                    // Show brief flash on live indicator
                    const indicator = document.getElementById('status-indicator');
                    indicator.classList.add('scale-110');
                    setTimeout(() => {
                        indicator.classList.remove('scale-110');
                    }, 300);
                }
            })
            .catch(error => {
                console.error('Polling error:', error);
                // Change indicator to show error
                const indicator = document.getElementById('status-indicator');
                const dot = indicator.querySelector('.bg-green-500');
                const text = indicator.querySelector('.text-green-600');
                dot.classList.remove('bg-green-500', 'animate-pulse');
                dot.classList.add('bg-red-500');
                text.classList.remove('text-green-600');
                text.classList.add('text-red-600');
                text.textContent = 'Error';
            });
    }

    // Start polling when page loads
    document.addEventListener('DOMContentLoaded', () => {
        if (isFirstPage) {
            pollingInterval = setInterval(checkNewPermohonan, 3000); // Poll every 3 seconds
        }
    });

    // Stop polling when page is hidden, resume when visible
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            clearInterval(pollingInterval);
        } else if (isFirstPage) {
            pollingInterval = setInterval(checkNewPermohonan, 3000);
            // Check immediately when tab becomes visible
            checkNewPermohonan();
        }
    });

    // Clean up on page unload
    window.addEventListener('beforeunload', () => {
        clearInterval(pollingInterval);
    });
</script>

<style>
    @keyframes slide-in {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-slide-in {
        animation: slide-in 0.5s ease-out;
    }

    #status-indicator {
        transition: transform 0.3s ease;
    }

    #status-indicator.scale-110 {
        transform: scale(1.1);
    }
</style>
@endsection