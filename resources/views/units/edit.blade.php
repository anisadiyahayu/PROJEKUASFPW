@extends('layouts.simship')

@section('title', 'Edit Satuan')
@section('crumb', 'Master Satuan / Edit')

@section('content')
    <h1>Edit Satuan</h1>
    <p class="subtitle">Perbarui kode atau nama satuan pekerjaan.</p>

    <div class="card" style="max-width:760px;">
        <form method="POST" action="{{ route('admin.units.update', $unit) }}">
            @csrf
            @method('PUT')
            @include('units._form', ['submitLabel' => 'Simpan Perubahan'])
        </form>
    </div>
@endsection
