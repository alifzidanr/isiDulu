@extends('layouts.app')

@section('title', 'Master Detail Perawatan - HelpDesk')
@section('page', 'Master Data / Detail Perawatan')

@section('content')
    @include('master.partials.simple-crud', [
        'items' => $detailPerawatans,
        'title' => 'Detail Perawatan',
        'noun' => 'detail perawatan',
        'description' => 'Rincian tindakan perawatan yang dipilih saat membuat laporan.',
        'route' => 'master.detail-perawatan',
        'idField' => 'id_detail_perawatan',
        'field' => 'nama_detail_perawatan',
        'label' => 'Nama Detail Perawatan',
        'icon' => 'list-checks',
    ])
@endsection
