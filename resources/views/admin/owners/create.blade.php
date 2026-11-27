@extends('layouts.simship')

@section('title', 'Tambah Pemilik Kapal')
@section('crumb', 'Data Pemilik Kapal')

@section('content')
    <h1>Tambah Pemilik Kapal</h1>
    <p class="subtitle">Lengkapi data pemilik sebelum menautkannya ke kapal.</p>

    <div class="card" style="max-width: 760px;">
        @include('admin.owners._form')
    </div>
@endsection
