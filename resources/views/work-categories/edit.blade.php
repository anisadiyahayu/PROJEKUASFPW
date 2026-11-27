@extends('layouts.simship')

@section('title', 'Edit Kategori Pekerjaan')
@section('crumb', 'Kategori Pekerjaan / Edit')

@section('content')
    <h1>Edit Kategori Pekerjaan</h1>
    <p class="subtitle">Perbarui nama atau deskripsi kategori.</p>

    <div class="card" style="max-width:760px;">
        <form method="POST" action="{{ route('admin.work-categories.update', $workCategory) }}">
            @csrf
            @method('PUT')
            @include('work-categories._form', ['submitLabel' => 'Simpan Perubahan'])
        </form>
    </div>
@endsection
