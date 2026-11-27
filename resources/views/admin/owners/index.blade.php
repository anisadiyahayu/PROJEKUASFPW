@extends('layouts.simship')

@section('title', 'Data Pemilik Kapal')
@section('crumb', 'Data Pemilik Kapal')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap;">
        <div>
            <h1>Data Pemilik Kapal</h1>
            <p class="subtitle">Kelola pemilik yang terdaftar beserta jumlah kapal miliknya.</p>
        </div>
        <a href="{{ route('admin.owners.create') }}" class="btn btn-primary">+ Tambah Pemilik</a>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('admin.owners.index') }}" class="filters">
            <div style="flex: 1; min-width: 240px;">
                <label for="q">Pencarian</label>
                <input id="q" type="text" name="q" value="{{ $q }}" placeholder="Cari nama atau alamat pemilik...">
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Cari</button>
            </div>
            @if ($q !== '')
                <div>
                    <a href="{{ route('admin.owners.index') }}" class="btn btn-light">Reset</a>
                </div>
            @endif
        </form>
    </div>

    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>NAMA PEMILIK</th>
                        <th>ALAMAT</th>
                        <th>JUMLAH KAPAL</th>
                        <th>TERDAFTAR</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($owners as $owner)
                        <tr>
                            <td><strong>{{ $owner->name }}</strong></td>
                            <td>{{ $owner->address ?: '-' }}</td>
                            <td>{{ $owner->ships_count }}</td>
                            <td>{{ $owner->created_at?->format('d M Y') }}</td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('admin.ships.index', ['owner_id' => $owner->id]) }}" class="btn btn-light btn-sm">Lihat Kapal</a>
                                    <a href="{{ route('admin.owners.edit', $owner) }}" class="btn btn-light btn-sm">Edit</a>
                                    <form method="POST" action="{{ route('admin.owners.destroy', $owner) }}"
                                          onsubmit="return confirm('Hapus pemilik {{ $owner->name }}? Tindakan ini dapat dipulihkan oleh admin.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty">Belum ada data pemilik.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; border-top: 1px solid #e2e8f0;">
            <span class="muted" style="font-size: 12px;">
                @if ($owners->total() > 0)
                    Menampilkan {{ $owners->firstItem() }}-{{ $owners->lastItem() }} dari {{ $owners->total() }} data
                @else
                    Menampilkan 0 data
                @endif
            </span>
            <div class="pagination">{{ $owners->onEachSide(1)->links() }}</div>
        </div>
    </div>
@endsection
