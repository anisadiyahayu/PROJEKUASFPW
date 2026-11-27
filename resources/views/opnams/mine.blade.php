@extends('layouts.simship')

@section('title', 'Opname Saya')
@section('crumb', 'Opname Saya')

@section('content')
    @php
        $statusLabels = [
            'draft' => 'Draft',
            'menunggu_review' => 'Menunggu Review',
            'perlu_revisi' => 'Perlu Revisi',
            'menunggu_approval_direksi' => 'Menunggu Approval Direksi',
            'ditolak' => 'Ditolak',
            'disetujui' => 'Disetujui',
            'dokumen_terbit' => 'Dokumen Terbit',
        ];
    @endphp

    <style>
        .opnam-table th { font-size: 11px; }
        .opnam-table td { font-size: 12px; line-height: 1.35; }
        .opnam-table td.angka { white-space: nowrap; }
    </style>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <div>
            <h1>Opname Saya</h1>
            <p class="subtitle">Kelola seluruh transaksi opnam yang Anda buat.</p>
        </div>
        <a href="{{ route('petugas.opnams.create') }}" class="btn btn-primary">+ Buat Opname</a>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('petugas.opnams.index') }}" style="display:flex; gap:10px; flex-wrap:wrap;">
            <div style="flex:1; min-width:220px;">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nomor opname atau nama kapal...">
            </div>
            <div style="width:180px;">
                <select name="status" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $statusLabels[$status] }}</option>
                    @endforeach
                </select>
            </div>
            <div style="width:200px;">
                <input id="filter-tanggal-opname" type="date" name="tanggal" value="{{ request('tanggal') }}" aria-label="Filter tanggal opname" title="Mencari berdasarkan Tanggal Opname yang diisi saat pencatatan, bukan tanggal pembuatan laporan" onchange="this.form.submit()">
            </div>
        </form>
    </div>

    <div class="card" style="padding:0; overflow:hidden;">
        <table class="opnam-table">
            <thead>
                <tr>
                    <th>NOMOR OPNAME</th>
                    <th>NAMA KAPAL</th>
                    <th>TANGGAL OPNAME</th>
                    <th>ESTIMASI BIAYA</th>
                    <th>STATUS</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($opnams as $opnam)
                    <tr>
                        <td class="angka"><strong style="color:#1d4ed8;">{{ $opnam->opnam_number }}</strong></td>
                        <td>{{ $opnam->ship?->name }}</td>
                        <td class="angka">{{ $opnam->tanggal_opnam?->format('d M Y') }}</td>
                        <td class="angka">Rp {{ number_format((float) $opnam->total_biaya, 0, ',', '.') }}</td>
                        <td>
                            @php
                                $tanpaStatus = request()->except('status');
                                $denganStatus = array_merge($tanpaStatus, ['status' => $opnam->status]);
                            @endphp
                            <a href="{{ route('petugas.opnams.index', request('status') === $opnam->status ? $tanpaStatus : $denganStatus) }}"
                               class="badge badge-{{ $opnam->status }}" title="Klik untuk filter status">{{ $statusLabels[$opnam->status] ?? $opnam->status }}</a>
                        </td>
                        <td>
                            @if ($opnam->status === 'draft')
                                <a href="{{ route('petugas.opnams.edit', $opnam) }}" class="btn btn-light" style="padding:5px 8px;">✎ Edit Item</a>
                            @else
                                <span class="btn btn-light" style="padding:5px 8px; opacity:.5;" title="Detail belum tersedia">👁 Detail</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center; color:#94a3b8;">Belum ada opnam yang Anda buat.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 20px; border-top:1px solid #e2e8f0;">
            <span style="color:#64748b; font-size:12px;">
                @if ($opnams->total() > 0)
                    Menampilkan {{ $opnams->firstItem() }}-{{ $opnams->lastItem() }} dari {{ $opnams->total() }} data
                @else
                    Menampilkan 0 data
                @endif
            </span>
            <div class="pagination">{{ $opnams->onEachSide(1)->links() }}</div>
        </div>
    </div>
@endsection
