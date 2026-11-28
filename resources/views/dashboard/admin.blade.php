@extends('layouts.simship')

@section('title', 'Dashboard Admin')
@section('crumb', 'Dashboard')

@section('content')
    <h1>Dashboard Admin</h1>
    <p class="subtitle">Pilih menu untuk memproses opnam dan invoice.</p>

    <div class="stat-grid">
        <div class="stat"><small>Menunggu Review</small><strong>{{ $antrean[\App\Models\Opnam::STATUS_MENUNGGU_REVIEW] }}</strong></div>
        <div class="stat"><small>Perlu Revisi</small><strong>{{ $antrean[\App\Models\Opnam::STATUS_PERLU_REVISI] }}</strong></div>
        <div class="stat"><small>Disetujui</small><strong>{{ $antrean[\App\Models\Opnam::STATUS_DISETUJUI] }}</strong></div>
        <div class="stat"><small>Pengguna Aktif</small><strong>{{ $penggunaAktif }}</strong></div>
        <div class="stat"><small>Pengguna Nonaktif</small><strong>{{ $penggunaNonaktif }}</strong></div>
    </div>

    <div class="grid">
        <div class="card">
            <h3 style="margin:0 0 8px;">Review Opname</h3>
            <p class="muted" style="margin:0 0 14px;">Periksa opnam yang menunggu review, lalu teruskan atau kembalikan.</p>
            <a href="{{ route('admin.review-opnams.index') }}" class="btn btn-primary">Buka Review Opname</a>
        </div>
        <div class="card">
            <h3 style="margin:0 0 8px;">Invoice</h3>
            <p class="muted" style="margin:0 0 14px;">Lihat draf invoice, lakukan revisi sebelum terbit, atau ekspor ke PDF.</p>
            <a href="{{ route('admin.invoices.index') }}" class="btn btn-light">Buka Daftar Invoice</a>
        </div>
        <div class="card">
            <h3 style="margin:0 0 8px;">Data Opname</h3>
            <p class="muted" style="margin:0 0 14px;">Pantau seluruh transaksi opnam yang diajukan Operator.</p>
            <a href="{{ route('admin.opnams.index') }}" class="btn btn-light">Buka Data Opname</a>
        </div>
    </div>
@endsection
