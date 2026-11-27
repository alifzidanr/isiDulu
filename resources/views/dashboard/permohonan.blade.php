@extends('layouts.app')

@section('title', 'Permohonan - HelpDesk')
@section('page', 'Permohonan')

@php
    $reopen = old('_form');
    $oldIf = fn ($form, $key) => $reopen === $form ? old($key) : null;
    // Actions offered per current status: [new status, menu label, confirm verb, icon]
    $actionsFor = fn ($p) => array_values(array_filter([
        $p->status_permohonan == 0 ? [1, 'Kerjakan', 'Kerjakan', 'play'] : null,
        $p->status_permohonan == 1 ? [2, 'Tandai selesai', 'Selesaikan', 'circle-check'] : null,
        $p->status_permohonan == 2 ? [4, 'Sahkan', 'Sahkan', 'badge-check'] : null,
    ]));
@endphp

@section('content')
<div class="grid gap-6"
     x-data="{
        createOpen: @js($reopen === 'create'),
        laporanOpen: @js(str_starts_with((string) $reopen, 'laporan:')),
        laporanId: @js(str_starts_with((string) $reopen, 'laporan:') ? substr($reopen, 8) : null),
        laporanFor: '',
        openLaporan(id, nama) { this.laporanId = id; this.laporanFor = nama; this.laporanOpen = true },
        setStatus(url, status, verb, nama) {
            toast.confirm({
                title: verb + ' permohonan ini?',
                description: 'Permohonan dari ' + nama + '.',
                confirmLabel: verb,
                destructive: status === 5,
                onConfirm: () => {
                    const form = this.$refs.statusForm;
                    form.action = url;
                    form.querySelector('[name=status]').value = status;
                    form.submit();
                },
            });
        },
     }">

    <x-ui.page-header title="Permohonan" description="Kelola permohonan layanan yang masuk.">
        <x-slot:actions>
            <x-ui.button icon="plus" x-on:click="createOpen = true">Buat Permohonan</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- One shared form for status changes; setStatus() fills in the URL and value. --}}
    <form x-ref="statusForm" method="POST" class="hidden">
        @csrf
        @method('PATCH')
        <input type="hidden" name="status">
    </form>

    <x-ui.card flush>
        <x-ui.table>
            <x-ui.table.header>
                <x-ui.table.head>Tanggal</x-ui.table.head>
                <x-ui.table.head>Pemohon</x-ui.table.head>
                <x-ui.table.head>Unit</x-ui.table.head>
                <x-ui.table.head class="min-w-64">Keterangan</x-ui.table.head>
                <x-ui.table.head>Status</x-ui.table.head>
                <x-ui.table.head class="text-center">Laporan</x-ui.table.head>
                <x-ui.table.head class="w-16 text-right"><span class="sr-only">Aksi</span></x-ui.table.head>
            </x-ui.table.header>
            <x-ui.table.body>
                @forelse ($permohonans as $permohonan)
                    @php $actions = $actionsFor($permohonan); @endphp
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
                        <x-ui.table.cell class="text-center">
                            @if ($permohonan->laporan)
                                <x-ui.button :href="route('permohonan.laporan.show', $permohonan->id_permohonan)" variant="ghost" size="icon-sm" icon="file-text" title="Lihat laporan">
                                    <span class="sr-only">Lihat laporan</span>
                                </x-ui.button>
                            @else
                                <span class="text-muted-foreground">—</span>
                            @endif
                        </x-ui.table.cell>
                        <x-ui.table.cell class="text-right">
                            @if (in_array($permohonan->status_permohonan, [3, 5]))
                                <span class="text-muted-foreground text-xs">—</span>
                            @else
                                @php $statusUrl = route('permohonan.status', $permohonan->id_permohonan); @endphp
                                <x-ui.dropdown-menu align="end" width="w-48">
                                    <x-slot:trigger>
                                        <x-ui.button variant="ghost" size="icon-sm" icon="ellipsis"><span class="sr-only">Buka aksi</span></x-ui.button>
                                    </x-slot:trigger>
                                    <x-ui.dropdown-menu.label>Ubah status</x-ui.dropdown-menu.label>
                                    @foreach ($actions as [$to, $label, $verb, $icon])
                                        <x-ui.dropdown-menu.item :icon="$icon"
                                            x-on:click="setStatus({{ Js::from($statusUrl) }}, {{ $to }}, {{ Js::from($verb) }}, {{ Js::from($permohonan->nama_pemohon) }})">{{ $label }}</x-ui.dropdown-menu.item>
                                    @endforeach
                                    @if ($permohonan->status_permohonan == 2 && ! $permohonan->laporan)
                                        <x-ui.dropdown-menu.item icon="file-plus"
                                            x-on:click="openLaporan({{ $permohonan->id_permohonan }}, {{ Js::from($permohonan->nama_pemohon) }})">Buat laporan</x-ui.dropdown-menu.item>
                                    @endif
                                    @if (count($actions) || ($permohonan->status_permohonan == 2 && ! $permohonan->laporan))
                                        <x-ui.dropdown-menu.separator />
                                    @endif
                                    <x-ui.dropdown-menu.item variant="destructive" icon="ban"
                                        x-on:click="setStatus({{ Js::from($statusUrl) }}, 5, 'Batalkan', {{ Js::from($permohonan->nama_pemohon) }})">Batalkan</x-ui.dropdown-menu.item>
                                </x-ui.dropdown-menu>
                            @endif
                        </x-ui.table.cell>
                    </x-ui.table.row>
                @empty
                    <x-ui.table.empty colspan="7" title="Belum ada permohonan" description="Permohonan baru akan muncul di sini." />
                @endforelse
            </x-ui.table.body>
        </x-ui.table>
        @if ($permohonans->total() > 0)
            <div class="border-t px-6 py-4">{{ $permohonans->links() }}</div>
        @endif
    </x-ui.card>

    {{-- Create permohonan --}}
    <x-ui.dialog model="createOpen" title="Buat Permohonan" description="Catat permohonan layanan atas nama pemohon." size="lg">
        <form id="createForm" method="POST" action="{{ route('permohonan.store') }}" class="grid gap-4 sm:grid-cols-2">
            @csrf
            <input type="hidden" name="_form" value="create">
            <x-ui.field label="Nama" for="nama_pemohon" name="nama_pemohon" form="create" required>
                <x-ui.input id="nama_pemohon" name="nama_pemohon" :value="$oldIf('create', 'nama_pemohon')" placeholder="Nama lengkap pemohon" required />
            </x-ui.field>
            <x-ui.field label="Email" for="email_pemohon" name="email_pemohon" form="create">
                <x-ui.input type="email" id="email_pemohon" name="email_pemohon" :value="$oldIf('create', 'email_pemohon')" placeholder="nama@email.com" />
            </x-ui.field>
            <x-ui.field label="No. Handphone" for="kontak_pemohon" name="kontak_pemohon" form="create" required>
                <x-ui.input id="kontak_pemohon" name="kontak_pemohon" :value="$oldIf('create', 'kontak_pemohon')" placeholder="08xxxxxxxxxx" inputmode="tel" required />
            </x-ui.field>
            <x-ui.field label="Nama Pimpinan" for="pimpinan_pemohon" name="pimpinan_pemohon" form="create">
                <x-ui.input id="pimpinan_pemohon" name="pimpinan_pemohon" :value="$oldIf('create', 'pimpinan_pemohon')" placeholder="Pimpinan unit (opsional)" />
            </x-ui.field>
            <x-ui.field label="Unit / Sub Unit" for="unit-picker" name="id_unit" form="create" required class="min-w-0">
                <x-unit-picker :units="$units" :unit="$oldIf('create', 'id_unit')" :sub-unit="$oldIf('create', 'id_sub_unit')" :invalid="$errors->has('id_unit') || $errors->has('id_sub_unit')" />
            </x-ui.field>
            <x-ui.field label="Inventaris" for="inventaris" name="inventaris" form="create" required>
                <x-ui.select id="inventaris" name="inventaris" placeholder="Pilih status" :options="['y' => 'Inventaris', 'n' => 'Non-Inventaris']" :selected="$oldIf('create', 'inventaris')" required />
            </x-ui.field>
            <x-ui.field label="Permohonan" for="keluhan" name="keluhan" form="create" required class="sm:col-span-2">
                <x-ui.textarea id="keluhan" name="keluhan" placeholder="Jelaskan kebutuhan atau kendala dengan detail..." required>{{ $oldIf('create', 'keluhan') }}</x-ui.textarea>
            </x-ui.field>
        </form>
        <x-slot:footer>
            <x-ui.button variant="outline" x-on:click="createOpen = false">Batal</x-ui.button>
            <x-ui.button type="submit" form="createForm" icon="send">Kirim Permohonan</x-ui.button>
        </x-slot:footer>
    </x-ui.dialog>

    {{-- Create laporan --}}
    <x-ui.dialog model="laporanOpen" title="Buat Laporan" size="xl">
        <p class="text-muted-foreground -mt-2 text-sm" x-show="laporanFor">Laporan pekerjaan untuk permohonan dari <span class="text-foreground font-medium" x-text="laporanFor"></span>.</p>
        <form id="laporanForm" method="POST" x-bind:action="{{ Js::from(route('permohonan.laporan.store', '__ID__')) }}.replace('__ID__', laporanId)" class="grid gap-4 sm:grid-cols-2">
            @csrf
            <input type="hidden" name="_form" x-bind:value="'laporan:' + laporanId">
            <x-ui.field label="Jenis Perangkat" for="id_jenis_perangkat" name="id_jenis_perangkat" form="laporan" required>
                <x-ui.select id="id_jenis_perangkat" name="id_jenis_perangkat" placeholder="Pilih jenis perangkat"
                    :options="$jenisPerangkats->pluck('nama_perangkat', 'id_jenis_perangkat')" :selected="old('id_jenis_perangkat')" required />
            </x-ui.field>
            <x-ui.field label="Detail Perangkat" for="detail_perangkat" name="detail_perangkat" form="laporan" required>
                <x-ui.input id="detail_perangkat" name="detail_perangkat" :value="old('detail_perangkat')" placeholder="Misal: HP Pavilion x360, AC Daikin 1.5 PK" required />
            </x-ui.field>
            <x-ui.field label="Jenis Perawatan" for="id_perawatan" name="id_perawatan" form="laporan" required>
                <x-ui.select id="id_perawatan" name="id_perawatan" placeholder="Pilih jenis perawatan"
                    :options="$jenisPerawatans->pluck('nama_perawatan', 'id_perawatan')" :selected="old('id_perawatan')" required />
            </x-ui.field>
            <x-ui.field label="Detail Perawatan" for="id_detail_perawatan" name="id_detail_perawatan" form="laporan" required>
                <x-ui.select id="id_detail_perawatan" name="id_detail_perawatan" placeholder="Pilih detail perawatan"
                    :options="$detailPerawatans->pluck('nama_detail_perawatan', 'id_detail_perawatan')" :selected="old('id_detail_perawatan')" required />
            </x-ui.field>
            <x-ui.field label="Uraian Pekerjaan" for="uraian_pekerjaan" name="uraian_pekerjaan" form="laporan" required class="sm:col-span-2">
                <x-ui.textarea id="uraian_pekerjaan" name="uraian_pekerjaan" placeholder="Jelaskan pekerjaan yang telah dilakukan..." required>{{ old('uraian_pekerjaan') }}</x-ui.textarea>
            </x-ui.field>
            <x-ui.field label="Catatan" for="catatan" name="catatan" form="laporan" class="sm:col-span-2">
                <x-ui.textarea id="catatan" name="catatan" rows="3" placeholder="Catatan tambahan (opsional)...">{{ old('catatan') }}</x-ui.textarea>
            </x-ui.field>
        </form>
        <x-slot:footer>
            <x-ui.button variant="outline" x-on:click="laporanOpen = false">Batal</x-ui.button>
            <x-ui.button type="submit" form="laporanForm" icon="save">Simpan Laporan</x-ui.button>
        </x-slot:footer>
    </x-ui.dialog>
</div>
@endsection
