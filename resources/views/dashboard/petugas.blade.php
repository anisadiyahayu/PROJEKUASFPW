@extends('layouts.simship')

@section('title', 'Dashboard Petugas')
@section('crumb', 'Dashboard')

@section('content')
    <h1>Dashboard Petugas</h1>
    <p class="subtitle">Ringkasan opnam yang Anda buat.</p>

    <div class="stat-grid">
        <div class="stat"><small>Draft</small><strong>{{ $ringkasan[\App\Models\Opnam::STATUS_DRAFT] }}</strong></div>
        <div class="stat"><small>Menunggu Review</small><strong>{{ $ringkasan[\App\Models\Opnam::STATUS_MENUNGGU_REVIEW] }}</strong></div>
        <div class="stat"><small>Perlu Revisi</small><strong>{{ $ringkasan[\App\Models\Opnam::STATUS_PERLU_REVISI] }}</strong></div>
        <div class="stat"><small>Disetujui</small><strong>{{ $ringkasan[\App\Models\Opnam::STATUS_DISETUJUI] }}</strong></div>
    </div>

    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:12px;">
            <h2 style="font-size:16px; margin:0;">Opname Terbaru</h2>
            <a href="{{ route('petugas.opnams.index') }}" class="btn btn-light btn-sm">Lihat semua</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>NOMOR</th><th>STATUS</th><th>DIBUAT</th><th>AKSI</th></tr>
                </thead>
                <tbody>
                    @forelse ($terbaru as $opnam)
                        <tr>
                            <td><strong>{{ $opnam->opnam_number }}</strong></td>
                            <td><span class="badge badge-{{ $opnam->status }}">{{ $opnam->status }}</span></td>
                            <td>{{ $opnam->created_at?->format('d M Y') }}</td>
                            <td><a href="{{ route('petugas.opnams.show', $opnam) }}" class="btn btn-light btn-sm">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty">Belum ada opnam.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
