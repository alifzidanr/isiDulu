// Status badge for rows built in JavaScript (the live public list). Mirrors
// resources/views/components/status-badge.blade.php; keep the two in step.
const STATUSES = {
    0: ['Permohonan', 'clock', 'border-amber-200 bg-amber-50 text-amber-700'],
    1: ['Dikerjakan', 'play', 'border-blue-200 bg-blue-50 text-blue-700'],
    2: ['Selesai', 'circle-check', 'border-emerald-200 bg-emerald-50 text-emerald-700'],
    3: ['Diarsipkan', 'inbox', 'border-transparent bg-muted text-muted-foreground'],
    4: ['Disahkan', 'badge-check', 'border-violet-200 bg-violet-50 text-violet-700'],
    5: ['Dibatalkan', 'ban', 'border-red-200 bg-red-50 text-red-700'],
};

export function statusBadge(status) {
    const [label, icon, classes] = STATUSES[status] ?? ['Unknown', 'info', 'text-foreground'];
    const badge = document.createElement('span');
    badge.className = `inline-flex w-fit shrink-0 items-center justify-center gap-1 overflow-hidden rounded-md border px-2 py-0.5 text-xs font-medium whitespace-nowrap ${classes}`;
    const i = document.createElement('i');
    i.dataset.lucide = icon;
    i.className = 'size-3';
    badge.append(i, label);
    return badge;
}
