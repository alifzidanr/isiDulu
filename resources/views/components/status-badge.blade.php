{{--
    Badge for a permohonan's status_permohonan code. The same mapping lives in
    resources/js/permohonan-status.js for rows added live by JavaScript; keep
    the two in step.
--}}
@props(['status'])

@php
    [$variant, $label, $icon] = match ((int) $status) {
        0 => ['warning', 'Permohonan', 'clock'],
        1 => ['info', 'Dikerjakan', 'play'],
        2 => ['success', 'Selesai', 'circle-check'],
        3 => ['muted', 'Diarsipkan', 'inbox'],
        4 => ['purple', 'Disahkan', 'badge-check'],
        5 => ['danger', 'Dibatalkan', 'ban'],
        default => ['outline', 'Unknown', 'info'],
    };
@endphp

<x-ui.badge :variant="$variant" {{ $attributes }}>
    <x-ui.icon :name="$icon" class="size-3" />
    {{ $label }}
</x-ui.badge>
