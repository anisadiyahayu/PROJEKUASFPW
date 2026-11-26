@extends('layouts.simship')

@section('title', 'Data Opname')
@section('crumb', 'Data Opname')

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

    <h1>Data Opname</h1>
    <p class="subtitle">Pantau seluruh transaksi opnam yang diajukan Operator.</p>

    <div class="card">
        <form method="GET" action="{{ route('admin.opnams.index') }}" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 220px;">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nomor opname atau nama kapal...">
            </div>
            <div style="width: 180px;">
                <select name="status">
                    <option value="">Semua Status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $statusLabels[$status] }}</option>
                    @endforeach
                </select>
            </div>
            <div style="width: 170px;">
                <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" aria-label="Tanggal dari">
            </div>
            <div style="width: 170px;">
                <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" aria-label="Tanggal sampai">
            </div>
            <div style="width: 90px;"><button type="submit" class="btn btn-primary">Filter</button></div>
        </form>
    </div>

    <div class="card" style="padding: 0; overflow: hidden;">
        <table>
            <thead>
                <tr>
                    <th>NOMOR OPNAME</th>
                    <th>NAMA KAPAL</th>
                    <th>OPERATOR</th>
                    <th>TANGGAL OPNAME</th>
                    <th>ESTIMASI BIAYA</th>
                    <th>STATUS</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($opnams as $opnam)
                    <tr>
                        <td><strong style="color:#1d4ed8;">{{ $opnam->opnam_number }}</strong></td>
                        <td>{{ $opnam->ship?->name }}</td>
                        <td>{{ $opnam->creator?->name }}</td>
                        <td>{{ $opnam->tanggal_opnam?->format('d M Y') }}</td>
                        <td>Rp {{ number_format((float) $opnam->total_biaya, 0, ',', '.') }}</td>
                        <td><span class="badge badge-{{ $opnam->status }}">{{ $statusLabels[$opnam->status] ?? $opnam->status }}</span></td>
                        <td>
                            <span class="btn btn-light" style="padding:5px 8px;" title="Detail belum tersedia">👁</span>
                            <span class="btn btn-light" style="padding:5px 8px;" title="Riwayat belum tersedia">◷</span>
                            <span class="btn btn-light" style="padding:5px 8px; opacity:.5;">Buka Review</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align:center; color:#94a3b8;">Belum ada opnam.</td></tr>
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
