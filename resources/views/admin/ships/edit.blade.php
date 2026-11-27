@extends('layouts.simship')

@section('title', 'Edit Kapal')
@section('crumb', 'Data Kapal')

@section('content')
    <h1>Edit Kapal</h1>
    <p class="subtitle">Perubahan data kapal tercatat pada riwayat aktivitas.</p>

    <div class="card" style="max-width: 760px;">
        @include('admin.ships._form')
    </div>
@endsection
