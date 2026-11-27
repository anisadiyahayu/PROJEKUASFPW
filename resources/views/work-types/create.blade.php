@extends('layouts.simship')

@section('title', 'Tambah Jenis Pekerjaan')
@section('crumb', 'Jenis Pekerjaan / Tambah')

@section('content')
    <h1>Tambah Jenis Pekerjaan</h1>
    <p class="subtitle">Pilih kategori dan masukkan nama jenis pekerjaan.</p>

    <div class="card" style="max-width:760px;">
        <form method="POST" action="{{ route('admin.work-types.store') }}">
            @csrf
            @include('work-types._form', ['submitLabel' => 'Simpan'])
        </form>
    </div>
@endsection
