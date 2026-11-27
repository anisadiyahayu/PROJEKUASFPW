@extends('layouts.simship')

@section('title', 'Edit Jenis Pekerjaan')
@section('crumb', 'Jenis Pekerjaan / Edit')

@section('content')
    <h1>Edit Jenis Pekerjaan</h1>
    <p class="subtitle">Perbarui kategori atau nama jenis pekerjaan.</p>

    <div class="card" style="max-width:760px;">
        <form method="POST" action="{{ route('admin.work-types.update', $workType) }}">
            @csrf
            @method('PUT')
            @include('work-types._form', ['submitLabel' => 'Simpan Perubahan'])
        </form>
    </div>
@endsection
