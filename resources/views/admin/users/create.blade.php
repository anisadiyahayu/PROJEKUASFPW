@extends('layouts.simship')

@section('title', 'Tambah Pengguna')
@section('crumb', 'Manajemen Pengguna / Tambah')

@section('content')
    <h1>Tambah Pengguna</h1>
    <p class="subtitle">Password minimal 8 karakter.</p>

    <div class="card">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            @include('admin.users._form', ['submitLabel' => 'Simpan'])
        </form>
    </div>
@endsection
