@extends('layouts.simship')

@section('title', 'Riwayat Perbaikan Kapal')
@section('crumb', 'Riwayat Perbaikan Kapal')

@section('content')
    <h1>Riwayat Perbaikan Kapal</h1>
    <p class="subtitle">
        @if ($onlyApproved)
            Daftar kapal dengan opnam yang sudah disetujui atau dokumennya terbit.
        @else
            Daftar kapal yang pernah memiliki opnam perbaikan.
        @endif
    </p>

    <div class="card">
        <form method="GET" action="{{ route('repair-history.index') }}" class="filters">
            <div style="flex: 1; min-width: 240px;">
                <label for="q">Pencarian</label>
                <input id="q" type="text" name="q" value="{{ $q }}" placeholder="Cari nama kapal atau nomor lambung...">
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Cari</button>
            </div>
            @if ($q !== '')
                <div>
                    <a href="{{ route('repair-history.index') }}" class="btn btn-light">Reset</a>
                </div>
            @endif
        </form>
    </div>

    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>NAMA KAPAL</th>
                        <th>NOMOR LAMBUNG</th>
                        <th>PEMILIK</th>
                        <th>JUMLAH OPNAM</th>
                        <th>OPNAM TERAKHIR</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ships as $ship)
                        <tr>
                            <td>
                                <strong>{{ $ship->name }}</strong>
                                @if ($ship->trashed())
                                    <span class="badge" style="margin-left: 6px; background: #fee2e2; color: #991b1b;">Dihapus</span>
                                @endif
                            </td>
                            <td>{{ $ship->hull_number }}</td>
                            <td>{{ $ship->owner?->name ?? '-' }}</td>
                            <td>{{ $ship->opnams_count }}</td>
                            <td>
                                {{ $ship->last_opnam_date
                                    ? \Illuminate\Support\Carbon::parse($ship->last_opnam_date)->format('d M Y')
                                    : '-' }}
                            </td>
                            <td>
                                <a href="{{ route('repair-history.show', $ship) }}" class="btn btn-light btn-sm">Lihat Riwayat</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty">
                                {{ $q !== '' ? 'Tidak ada kapal yang cocok dengan pencarian.' : 'Belum ada kapal dengan riwayat perbaikan.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; border-top: 1px solid #e2e8f0;">
            <span class="muted" style="font-size: 12px;">
                @if ($ships->total() > 0)
                    Menampilkan {{ $ships->firstItem() }}-{{ $ships->lastItem() }} dari {{ $ships->total() }} kapal
                @else
                    Menampilkan 0 kapal
                @endif
            </span>
            <div class="pagination">{{ $ships->onEachSide(1)->links() }}</div>
        </div>
    </div>
@endsection
