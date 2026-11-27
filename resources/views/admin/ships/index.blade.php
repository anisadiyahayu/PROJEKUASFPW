@extends('layouts.simship')

@section('title', 'Data Kapal')
@section('crumb', 'Data Kapal')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap;">
        <div>
            <h1>Data Kapal</h1>
            <p class="subtitle">Kelola kapal dan hubungkan setiap kapal dengan pemiliknya.</p>
        </div>
        <a href="{{ route('admin.ships.create') }}" class="btn btn-primary">+ Tambah Kapal</a>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('admin.ships.index') }}" class="filters">
            <div style="flex: 1; min-width: 220px;">
                <label for="q">Pencarian</label>
                <input id="q" type="text" name="q" value="{{ $q }}" placeholder="Cari nama kapal atau nomor lambung...">
            </div>
            <div style="min-width: 220px;">
                <label for="owner_id">Pemilik</label>
                <select id="owner_id" name="owner_id">
                    <option value="">Semua Pemilik</option>
                    @foreach ($owners as $owner)
                        <option value="{{ $owner->id }}" @selected($ownerId === $owner->id)>{{ $owner->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
            @if ($q !== '' || $ownerId !== null)
                <div>
                    <a href="{{ route('admin.ships.index') }}" class="btn btn-light">Reset</a>
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
                        <th>JENIS</th>
                        <th>PEMILIK</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ships as $ship)
                        <tr>
                            <td><strong>{{ $ship->name }}</strong></td>
                            <td>{{ $ship->hull_number }}</td>
                            <td>{{ $ship->jenis_kapal ?: '-' }}</td>
                            <td>{{ $ship->owner?->name ?? '-' }}</td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('repair-history.show', $ship) }}" class="btn btn-light btn-sm">Riwayat</a>
                                    <a href="{{ route('admin.ships.edit', $ship) }}" class="btn btn-light btn-sm">Edit</a>
                                    <form method="POST" action="{{ route('admin.ships.destroy', $ship) }}"
                                          onsubmit="return confirm('Hapus kapal {{ $ship->name }}? Riwayat opnam lama tetap tersimpan.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty">Belum ada data kapal.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; border-top: 1px solid #e2e8f0;">
            <span class="muted" style="font-size: 12px;">
                @if ($ships->total() > 0)
                    Menampilkan {{ $ships->firstItem() }}-{{ $ships->lastItem() }} dari {{ $ships->total() }} data
                @else
                    Menampilkan 0 data
                @endif
            </span>
            <div class="pagination">{{ $ships->onEachSide(1)->links() }}</div>
        </div>
    </div>
@endsection
