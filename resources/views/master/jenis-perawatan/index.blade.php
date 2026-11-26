@extends('layouts.app')

@section('title', 'Master Jenis Perawatan - HelpDesk')
@section('page', 'Master Data / Jenis Perawatan')

@section('content')
    @include('master.partials.simple-crud', [
        'items' => $jenisPerawatans,
        'title' => 'Jenis Perawatan',
        'noun' => 'jenis perawatan',
        'description' => 'Jenis pekerjaan perawatan yang dicatat di laporan.',
        'route' => 'master.jenis-perawatan',
        'idField' => 'id_perawatan',
        'field' => 'nama_perawatan',
        'label' => 'Nama Perawatan',
        'icon' => 'wrench',
    ])
@endsection
