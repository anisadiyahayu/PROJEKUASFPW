@extends('layouts.simship')

@section('title', 'Edit Pemilik Kapal')
@section('crumb', 'Data Pemilik Kapal')

@section('content')
    <h1>Edit Pemilik Kapal</h1>
    <p class="subtitle">Perubahan data pemilik tercatat pada riwayat aktivitas.</p>

    <div class="card" style="max-width: 760px;">
        @include('admin.owners._form')
    </div>
@endsection
