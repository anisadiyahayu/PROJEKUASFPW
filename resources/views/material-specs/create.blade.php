@extends('layouts.simship')

@section('title', 'Tambah Material')
@section('crumb', 'Master Material / Tambah')

@section('content')
    <h1>Tambah Material/Spesifikasi</h1>
    <p class="subtitle">Pilih jenis pekerjaan dan masukkan nama material/spesifikasi.</p>

    <div class="card" style="max-width:760px;">
        <form method="POST" action="{{ route('admin.material-specs.store') }}">
            @csrf
            @include('material-specs._form', ['submitLabel' => 'Simpan'])
        </form>
    </div>
@endsection
