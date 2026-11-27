@extends('layouts.simship')

@section('title', 'Tambah Kategori Pekerjaan')
@section('crumb', 'Kategori Pekerjaan / Tambah')

@section('content')
    <h1>Tambah Kategori Pekerjaan</h1>
    <p class="subtitle">Masukkan nama dan deskripsi kategori.</p>

    <div class="card" style="max-width:760px;">
        <form method="POST" action="{{ route('admin.work-categories.store') }}">
            @csrf
            @include('work-categories._form', ['submitLabel' => 'Simpan'])
        </form>
    </div>
@endsection
