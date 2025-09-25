<!-- resources/views/dashboard/index.blade.php -->
@extends('layouts.app')

@section('title', 'Dashboard - isiDulu')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
    
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-file-alt text-2xl text-blue-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Permohonan</dt>
                            <dd class="text-3xl font-semibold text-gray-900">{{ $stats['total_permohonan'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-clock text-2xl text-yellow-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Pending</dt>
                            <dd class="text-3xl font-semibold text-gray-900">{{ $stats['permohonan_pending'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-play text-2xl text-blue-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Dikerjakan</dt>
                            <dd class="text-3xl font-semibold text-gray-900">{{ $stats['permohonan_dikerjakan'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check text-2xl text-green-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Selesai</dt>
                            <dd class="text-3xl font-semibold text-gray-900">{{ $stats['permohonan_selesai'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Section - Made More Compact -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Chart Container -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Status Permohonan</h3>
            <div class="relative" style="height: 300px;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
        
        <!-- You can add another widget here -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Activity</h3>
            <div class="space-y-3">
                <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-circle text-xs text-green-500 mr-2"></i>
                    <span>5 permohonan diselesaikan hari ini</span>
                </div>
                <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-circle text-xs text-blue-500 mr-2"></i>
                    <span>12 permohonan dalam proses</span>
                </div>
                <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-circle text-xs text-yellow-500 mr-2"></i>
                    <span>8 permohonan menunggu persetujuan</span>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const ctx = document.getElementById('statusChart').getContext('2d');
const statusData = @json($statusData);

const labels = statusData.map(item => {
    switch(item.status_permohonan) {
        case 0: return 'Permohonan';
        case 1: return 'Dikerjakan';
        case 2: return 'Selesai';
        case 3: return 'Diarsipkan';
        case 4: return 'Disahkan';
        case 5: return 'Dibatalkan';
        default: return 'Unknown';
    }
});

const data = statusData.map(item => item.count);
const colors = ['#FCD34D', '#3B82F6', '#10B981', '#6B7280', '#8B5CF6', '#EF4444'];

new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: labels,
        datasets: [{
            data: data,
            backgroundColor: colors,
            borderWidth: 2,
            borderColor: '#ffffff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 12,
                    padding: 8,
                    font: {
                        size: 11
                    }
                }
            }
        }
    }
});
</script>
@endpush
@endsection