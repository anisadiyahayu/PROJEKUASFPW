@extends('layouts.simship')

@section('title', 'Daftar Tagihan')
@section('crumb', 'Tagihan')

@section('content')
    @php $rupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.'); @endphp

    <h1>Daftar Tagihan</h1>
    <p class="subtitle">Tagihan yang menunggu keputusan Direksi.</p>

    <div class="card">
        <form method="GET" action="{{ route('direksi.tagihans.index') }}" class="actions form-row" style="align-items:flex-end;">
            <div style="flex:1; min-width:220px;">
                <label for="q">Cari</label>
                <input id="q" type="text" name="q" value="{{ request('q') }}" placeholder="Nomor tagihan, nomor opnam, atau nama kapal">
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
                        <th>NOMOR TAGIHAN</th>
                        <th>NOMOR OPNAME</th>
                        <th>NAMA KAPAL</th>
                        <th>TANGGAL OPNAME</th>
                        <th>SUBTOTAL</th>
                        <th>PENYESUAIAN</th>
                        <th>TOTAL</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($billings as $billing)
                        <tr>
                            <td class="nowrap"><strong style="color:#1d4ed8;">{{ $billing->billing_number ?? '-' }}</strong></td>
                            <td class="nowrap">{{ $billing->opnam?->opnam_number ?? '-' }}</td>
                            <td>{{ $billing->opnam?->ship?->name ?? '-' }}</td>
                            <td class="nowrap">{{ $billing->opnam?->tanggal_opnam?->format('d M Y') ?? '-' }}</td>
                            <td class="nowrap">{{ $rupiah($billing->subtotal_amount) }}</td>
                            <td class="nowrap">{{ $rupiah($billing->adjustment_amount) }}</td>
                            <td class="nowrap"><strong>{{ $rupiah($billing->total_amount) }}</strong></td>
                            <td>
                                <a href="{{ route('direksi.tagihans.show', $billing) }}" class="btn btn-light" style="padding:6px 10px;">Periksa</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="empty">Belum ada tagihan yang menunggu approval.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 20px; border-top:1px solid #e2e8f0;">
            <span class="muted" style="font-size:12px;">Menampilkan {{ $billings->total() }} data</span>
            <div class="pagination">{{ $billings->onEachSide(1)->links() }}</div>
        </div>
    </div>
@endsection
