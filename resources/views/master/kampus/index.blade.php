@extends('layouts.app')

@section('title', 'Master Kampus - HelpDesk')
@section('page', 'Master Data / Kampus')

@section('content')
    @include('master.partials.simple-crud', [
        'items' => $kampuses,
        'title' => 'Kampus',
        'noun' => 'kampus',
        'description' => 'Daftar kampus yang dilayani HelpDesk.',
        'route' => 'master.kampus',
        'idField' => 'id_kampus',
        'field' => 'nama_kampus',
        'label' => 'Nama Kampus',
        'icon' => 'building-2',
    ])
@endsection
