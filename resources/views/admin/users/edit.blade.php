@extends('layouts.simship')

@section('title', 'Ubah Pengguna')
@section('crumb', 'Manajemen Pengguna / Ubah')

@section('content')
    <h1>Ubah Pengguna</h1>
    <p class="subtitle">Kosongkan password jika tidak ingin mengubahnya.</p>

    <div class="card">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')
            @include('admin.users._form', ['submitLabel' => 'Simpan Perubahan'])
        </form>
    </div>
@endsection
