@extends('layouts.app')

@section('title', 'Master Jenis Perangkat - HelpDesk')
@section('page', 'Master Data / Jenis Perangkat')

@section('content')
    @include('master.partials.simple-crud', [
        'items' => $jenisPerangkats,
        'title' => 'Jenis Perangkat',
        'noun' => 'jenis perangkat',
        'description' => 'Kategori perangkat yang bisa dilaporkan, misalnya laptop atau printer.',
        'route' => 'master.jenis-perangkat',
        'idField' => 'id_jenis_perangkat',
        'field' => 'nama_perangkat',
        'label' => 'Nama Perangkat',
        'icon' => 'laptop',
    ])
@endsection
