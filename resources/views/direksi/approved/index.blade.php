@extends('layouts.simship')

@section('title', 'Transaksi Disetujui')
@section('crumb', 'Transaksi Disetujui')

@section('content')
    <h1>Transaksi Disetujui</h1>
    <p class="subtitle">Opnam yang telah disetujui atau dokumennya sudah terbit. Buka riwayat untuk melihat detail perbaikan kapal.</p>

    <div class="card">
        <form method="GET" action="{{ route('direksi.approved.index') }}" class="filters">
            <div style="flex: 1; min-width: 240px;">
                <label for="q">Pencarian</label>
                <input id="q" type="text" name="q" value="{{ $q }}" placeholder="Nomor opnam, nama kapal, atau nomor lambung...">
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Cari</button>
            </div>
            @if ($q !== '')
                <div>
                    <a href="{{ route('direksi.approved.index') }}" class="btn btn-light">Reset</a>
                </div>
            @endif
        </form>
    </div>

    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>NOMOR OPNAM</th>
                        <th>TANGGAL</th>
                        <th>NAMA KAPAL</th>
                        <th>NOMOR LAMBUNG</th>
                        <th>PEMILIK</th>
                        <th>JUMLAH ITEM</th>
                        <th>TOTAL BIAYA</th>
                        <th>STATUS</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($opnams as $opnam)
                        <tr>
                            <td><strong style="color: #1d4ed8;">{{ $opnam->opnam_number }}</strong></td>
                            <td>{{ $opnam->tanggal_opnam?->format('d M Y') ?? '-' }}</td>
                            <td>
                                {{ $opnam->ship?->name ?? '-' }}
                                @if ($opnam->ship?->trashed())
                                    <span class="badge" style="margin-left: 6px; background: #fee2e2; color: #991b1b;">Dihapus</span>
                                @endif
                            </td>
                            <td>{{ $opnam->ship?->hull_number ?? '-' }}</td>
                            <td>{{ $opnam->ship?->owner?->name ?? '-' }}</td>
                            <td>{{ $opnam->items_count }}</td>
                            <td>Rp {{ number_format((float) $opnam->total_biaya, 0, ',', '.') }}</td>
                            <td><span class="badge badge-{{ $opnam->status }}">{{ \App\Support\OpnamStatus::label($opnam->status) }}</span></td>
                            <td>
                                @if ($opnam->ship)
                                    <a href="{{ route('repair-history.show', $opnam->ship) }}" class="btn btn-light btn-sm">Riwayat Kapal</a>
                                @else
                                    <span class="muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="empty">Belum ada transaksi yang disetujui.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; border-top: 1px solid #e2e8f0;">
            <span class="muted" style="font-size: 12px;">
                @if ($opnams->total() > 0)
                    Menampilkan {{ $opnams->firstItem() }}-{{ $opnams->lastItem() }} dari {{ $opnams->total() }} transaksi
                @else
                    Menampilkan 0 transaksi
                @endif
            </span>
            <div class="pagination">{{ $opnams->onEachSide(1)->links() }}</div>
        </div>
    </div>
@endsection
