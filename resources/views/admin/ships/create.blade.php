@extends('layouts.simship')

@section('title', 'Tambah Kapal')
@section('crumb', 'Data Kapal')

@section('content')
    <h1>Tambah Kapal</h1>
    <p class="subtitle">Setiap kapal wajib terhubung dengan satu pemilik.</p>

    <div class="card" style="max-width: 760px;">
        @include('admin.ships._form')
    </div>
@endsection
