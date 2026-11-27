@extends('layouts.simship')

@section('title', 'Tambah Satuan')
@section('crumb', 'Master Satuan / Tambah')

@section('content')
    <h1>Tambah Satuan</h1>
    <p class="subtitle">Masukkan kode dan nama satuan pekerjaan.</p>

    <div class="card" style="max-width:760px;">
        <form method="POST" action="{{ route('admin.units.store') }}">
            @csrf
            @include('units._form', ['submitLabel' => 'Simpan'])
        </form>
    </div>
@endsection
