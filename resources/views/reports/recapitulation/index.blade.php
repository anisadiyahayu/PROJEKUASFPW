@extends('layouts.simship')

@section('title', 'Laporan Rekapitulasi Pekerjaan')
@section('crumb', 'Laporan Rekapitulasi')

@section('content')
    @php
        $exportQuery = array_filter($filters, fn ($value) => $value !== '' && $value !== null);
    @endphp

    <h1>Laporan Rekapitulasi Pekerjaan</h1>
    <p class="subtitle">Rekap opnam berdasarkan periode tanggal opnam. Nilai biaya mengikuti snapshot opnam, bukan tarif master terkini.</p>

    <div class="card">
        <form method="GET" action="{{ route('reports.recapitulation.index') }}" class="filters">
            <div>
                <label for="dari">Dari Tanggal</label>
                <input id="dari" type="date" name="dari" value="{{ $filters['dari'] }}">
            </div>
            <div>
                <label for="sampai">Sampai Tanggal</label>
                <input id="sampai" type="date" name="sampai" value="{{ $filters['sampai'] }}">
            </div>
            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">Semua Status</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width: 200px;">
                <label for="ship_id">Kapal</label>
                <select id="ship_id" name="ship_id">
                    <option value="">Semua Kapal</option>
                    @foreach ($ships as $ship)
                        <option value="{{ $ship->id }}" @selected($filters['ship_id'] === $ship->id)>
                            {{ $ship->name }} — {{ $ship->hull_number }}@if ($ship->trashed()) (dihapus)@endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="flex: 1; min-width: 190px;">
                <label for="q">Pencarian</label>
                <input id="q" type="text" name="q" value="{{ $filters['q'] }}" placeholder="Nomor opnam, kapal, lambung...">
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Terapkan</button>
            </div>
            <div>
                <a href="{{ route('reports.recapitulation.index') }}" class="btn btn-light">Reset</a>
            </div>
        </form>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 12px;">
        <span class="muted" style="font-size: 12px;">Periode {{ \Illuminate\Support\Carbon::parse($filters['dari'])->format('d M Y') }} — {{ \Illuminate\Support\Carbon::parse($filters['sampai'])->format('d M Y') }}</span>
        @if ($canExport)
            <div class="actions">
                <a href="{{ route('reports.recapitulation.pdf', $exportQuery) }}" class="btn btn-light">Unduh PDF</a>
                <a href="{{ route('reports.recapitulation.excel', $exportQuery) }}" class="btn btn-primary">Unduh Excel</a>
            </div>
        @endif
    </div>

    <div class="stat-grid">
        <div class="stat">
            <small>Total Opnam</small>
            <strong>{{ number_format($summary['total_opnam'], 0, ',', '.') }}</strong>
        </div>
        <div class="stat">
            <small>Total Item Pekerjaan</small>
            <strong>{{ number_format($summary['total_item'], 0, ',', '.') }}</strong>
        </div>
        <div class="stat">
            <small>Total Biaya</small>
            <strong>Rp {{ number_format($summary['total_biaya'], 0, ',', '.') }}</strong>
        </div>
    </div>

    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>NO</th>
                        <th>NOMOR OPNAM</th>
                        <th>TANGGAL</th>
                        <th>NAMA KAPAL</th>
                        <th>NOMOR LAMBUNG</th>
                        <th>PEMILIK</th>
                        <th>STATUS</th>
                        <th>JUMLAH ITEM</th>
                        <th>TOTAL BIAYA</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $opnam)
                        <tr>
                            <td>{{ $rows->firstItem() + $loop->index }}</td>
                            <td><strong style="color: #1d4ed8;">{{ $opnam->opnam_number }}</strong></td>
                            <td>{{ $opnam->tanggal_opnam?->format('d M Y') ?? '-' }}</td>
                            <td>{{ $opnam->ship?->name ?? '-' }}</td>
                            <td>{{ $opnam->ship?->hull_number ?? '-' }}</td>
                            <td>{{ $opnam->ship?->owner?->name ?? '-' }}</td>
                            <td><span class="badge badge-{{ $opnam->status }}">{{ \App\Support\OpnamStatus::label($opnam->status) }}</span></td>
                            <td>{{ $opnam->items_count }}</td>
                            <td>Rp {{ number_format((float) $opnam->total_biaya, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="empty">Tidak ada opnam pada periode dan filter yang dipilih.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; border-top: 1px solid #e2e8f0;">
            <span class="muted" style="font-size: 12px;">
                @if ($rows->total() > 0)
                    Menampilkan {{ $rows->firstItem() }}-{{ $rows->lastItem() }} dari {{ $rows->total() }} data
                @else
                    Menampilkan 0 data
                @endif
            </span>
            <div class="pagination">{{ $rows->onEachSide(1)->links() }}</div>
        </div>
    </div>
@endsection
