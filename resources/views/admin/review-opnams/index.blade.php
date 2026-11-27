@extends('layouts.simship')

@section('title', 'Review Opname')
@section('crumb', 'Review Opname')

@section('content')
    @php $rupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.'); @endphp

    <h1>Review Opname</h1>
    <p class="subtitle">Daftar opnam yang menunggu pemeriksaan Admin.</p>

    <div class="card">
        <form method="GET" action="{{ route('admin.review-opnams.index') }}" class="actions form-row" style="align-items:flex-end;">
            <div style="flex:1; min-width:220px;">
                <label for="q">Cari</label>
                <input id="q" type="text" name="q" value="{{ request('q') }}" placeholder="Nomor opnam, nama kapal, atau nomor lambung">
            </div>
            <div style="width:170px;">
                <label for="tanggal_dari">Tanggal Dari</label>
                <input id="tanggal_dari" type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}">
            </div>
            <div style="width:170px;">
                <label for="tanggal_sampai">Tanggal Sampai</label>
                <input id="tanggal_sampai" type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}">
            </div>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>

    <div class="card" style="padding:0; overflow:hidden;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>NOMOR OPNAME</th>
                        <th>NAMA KAPAL</th>
                        <th>OPERATOR</th>
                        <th>TANGGAL</th>
                        <th>ESTIMASI BIAYA</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($opnams as $opnam)
                        <tr>
                            <td class="nowrap"><strong style="color:#1d4ed8;">{{ $opnam->opnam_number }}</strong></td>
                            <td>{{ $opnam->ship?->name ?? '-' }}</td>
                            <td>{{ $opnam->creator?->name ?? '-' }}</td>
                            <td class="nowrap">{{ $opnam->tanggal_opnam?->format('d M Y') ?? '-' }}</td>
                            <td class="nowrap">{{ $rupiah($opnam->total_biaya) }}</td>
                            <td>
                                <a href="{{ route('admin.review-opnams.show', $opnam) }}" class="btn btn-light" style="padding:6px 10px;">Periksa</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty">Belum ada opnam yang menunggu review.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 20px; border-top:1px solid #e2e8f0;">
            <span class="muted" style="font-size:12px;">Menampilkan {{ $opnams->total() }} data</span>
            <div class="pagination">{{ $opnams->onEachSide(1)->links() }}</div>
        </div>
    </div>
@endsection
