@extends('layouts.simship')

@section('title', 'Dashboard Direksi')
@section('crumb', 'Dashboard')

@section('content')
    <h1>Dashboard Direksi</h1>
    <p class="subtitle">Pilih menu untuk memproses tagihan dan dokumen yang diterbitkan.</p>

    <div class="stat-grid">
        <div class="stat"><small>Tagihan Menunggu Approval</small><strong>{{ $jumlahMenunggu }}</strong></div>
        <div class="stat"><small>Total Nilai Menunggu</small><strong>Rp {{ number_format($totalMenunggu, 0, ',', '.') }}</strong></div>
    </div>

    <div class="grid">
        <div class="card">
            <h3 style="margin:0 0 8px;">Tagihan</h3>
            <p class="muted" style="margin:0 0 14px;">Periksa tagihan, catat penyesuaian, lalu setujui atau tolak.</p>
            <a href="{{ route('direksi.tagihans.index') }}" class="btn btn-primary">Buka Daftar Tagihan</a>
        </div>
        <div class="card">
            <h3 style="margin:0 0 8px;">Dokumen</h3>
            <p class="muted" style="margin:0 0 14px;">Lihat invoice dan technical report, lalu unduh atau cetak.</p>
            <a href="{{ route('direksi.dokumen.index') }}" class="btn btn-light">Buka Daftar Dokumen</a>
        </div>
    </div>
@endsection
