@extends('layouts.simship')

@section('title', 'Edit Material')
@section('crumb', 'Master Material / Edit')

@section('content')
    <h1>Edit Material/Spesifikasi</h1>
    <p class="subtitle">Perbarui jenis pekerjaan atau nama material/spesifikasi.</p>

    <div class="card" style="max-width:760px;">
        <form method="POST" action="{{ route('admin.material-specs.update', $materialSpec) }}">
            @csrf
            @method('PUT')
            @include('material-specs._form', ['submitLabel' => 'Simpan Perubahan'])
        </form>
    </div>
@endsection
