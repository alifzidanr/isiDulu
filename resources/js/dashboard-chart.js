// Status doughnut on the dashboard. Its own Vite entry, so Chart.js is only
// downloaded on the page that uses it. Data comes from the canvas's
// data-status attribute: [{ status_permohonan, count }, ...].
import Chart from 'chart.js/auto';

const canvas = document.getElementById('statusChart');

if (canvas) {
    const labels = { 0: 'Permohonan', 1: 'Dikerjakan', 2: 'Selesai', 3: 'Diarsipkan', 4: 'Disahkan', 5: 'Dibatalkan' };
    // Same hues as the status badges (amber, blue, emerald, neutral, violet, red).
    const colors = { 0: '#f59e0b', 1: '#2563eb', 2: '#10b981', 3: '#a3a3a3', 4: '#8b5cf6', 5: '#ef4444' };
    const data = JSON.parse(canvas.dataset.status || '[]');

    new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: data.map((d) => labels[d.status_permohonan] ?? 'Unknown'),
            datasets: [{
                data: data.map((d) => d.count),
                backgroundColor: data.map((d) => colors[d.status_permohonan] ?? '#737373'),
                borderColor: '#ffffff',
                borderWidth: 2,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 14, font: { family: 'Inter', size: 12 } } },
                tooltip: { padding: 10, cornerRadius: 6, titleFont: { family: 'Inter' }, bodyFont: { family: 'Inter' } },
            },
        },
    });
}
