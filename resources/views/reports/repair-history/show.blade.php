@extends('layouts.simship')

@section('title', 'Riwayat Perbaikan '.$ship->name)
@section('crumb', 'Riwayat Perbaikan Kapal')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap;">
        <div>
            <h1>{{ $ship->name }}</h1>
            <p class="subtitle">
                Nomor lambung {{ $ship->hull_number }}
                &middot; Pemilik {{ $ship->owner?->name ?? '-' }}
                @if ($ship->jenis_kapal)
                    &middot; {{ $ship->jenis_kapal }}
                @endif
            </p>
            @if ($ship->trashed())
                <div class="alert-error" style="max-width: 520px;">
                    Kapal ini sudah dihapus dari data aktif. Riwayat opnam tetap ditampilkan.
                </div>
            @endif
        </div>
        <a href="{{ route('repair-history.index') }}" class="btn btn-light">&larr; Daftar Kapal</a>
    </div>

    @if ($onlyApproved)
        <div class="info" style="margin-bottom: 16px;">
            Direksi hanya melihat opnam berstatus Disetujui atau Dokumen Terbit.
        </div>
    @endif

    <div class="card">
        <form method="GET" action="{{ route('repair-history.show', $ship) }}" class="filters">
            <div style="min-width: 220px;">
                <label for="status">Status Opnam</label>
                <select id="status" name="status">
                    <option value="">Semua Status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected($statusFilter === $status)>
                            {{ \App\Support\OpnamStatus::label($status) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
            @if ($statusFilter !== '')
                <div>
                    <a href="{{ route('repair-history.show', $ship) }}" class="btn btn-light">Reset</a>
                </div>
            @endif
        </form>
    </div>

    @forelse ($opnams as $opnam)
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap; margin-bottom: 12px;">
                <div>
                    <strong style="color: #1d4ed8; font-size: 15px;">{{ $opnam->opnam_number }}</strong>
                    <div class="muted" style="font-size: 12px; margin-top: 2px;">
                        {{ $opnam->tanggal_opnam?->format('d M Y') ?? 'Tanggal belum diisi' }}
                        &middot; Operator {{ $opnam->creator?->name ?? '-' }}
                    </div>
                </div>
                <div style="text-align: right;">
                    <span class="badge badge-{{ $opnam->status }}">{{ \App\Support\OpnamStatus::label($opnam->status) }}</span>
                    <div style="font-weight: 700; color: #1e3a8a; margin-top: 6px;">
                        Rp {{ number_format((float) $opnam->total_biaya, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            @if ($opnam->catatan)
                <p class="muted" style="font-size: 12px; margin: 0 0 10px;">Catatan: {{ $opnam->catatan }}</p>
            @endif

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>NO</th>
                            <th>URAIAN PEKERJAAN</th>
                            <th>MATERIAL</th>
                            <th>SATUAN</th>
                            <th>VOLUME</th>
                            <th>HARGA SATUAN</th>
                            <th>SUBTOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($opnam->items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    {{ $item->tariff?->materialSpec?->workType?->name ?? $item->description ?? '-' }}
                                    @if ($item->description && $item->tariff?->materialSpec?->workType?->name)
                                        <div class="muted" style="font-size: 11px;">{{ $item->description }}</div>
                                    @endif
                                </td>
                                <td>{{ $item->tariff?->materialSpec?->name ?? '-' }}</td>
                                <td>{{ $item->tariff?->unit?->name ?? '-' }}</td>
                                <td>{{ number_format((float) $item->volume, 2, ',', '.') }}</td>
                                <td>Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty">Opnam ini belum memiliki item pekerjaan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="card">
            <p class="empty" style="margin: 0;">
                {{ $statusFilter !== '' ? 'Tidak ada opnam dengan status tersebut.' : 'Kapal ini belum memiliki opnam.' }}
            </p>
        </div>
    @endforelse

    @if ($opnams->hasPages())
        <div class="pagination">{{ $opnams->onEachSide(1)->links() }}</div>
    @endif
@endsection
