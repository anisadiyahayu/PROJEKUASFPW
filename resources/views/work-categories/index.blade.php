@extends('layouts.simship')

@section('title', 'Kategori Pekerjaan')
@section('crumb', 'Kategori Pekerjaan')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;">
        <div>
            <h1>Kategori Pekerjaan</h1>
            <p class="subtitle">Kelola kategori pekerjaan kapal.</p>
        </div>
        <a href="{{ route('admin.work-categories.create') }}" class="btn btn-primary">Tambah Kategori</a>
    </div>

    <div class="card" style="padding:0; overflow:hidden;">
        <table>
            <thead>
                <tr>
                    <th>NAMA KATEGORI</th>
                    <th>DESKRIPSI</th>
                    <th>STATUS</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td><strong>{{ $category->name }}</strong></td>
                        <td>{{ $category->description ?: '—' }}</td>
                        <td>{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('admin.work-categories.edit', $category) }}" class="btn btn-light">Edit</a>
                            <form method="POST" action="{{ route('admin.work-categories.destroy', $category) }}" style="display:inline;" onsubmit="return confirm('Hapus kategori ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-light">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align:center; color:#94a3b8;">Belum ada kategori pekerjaan.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 20px; border-top:1px solid #e2e8f0;">
            <span style="color:#64748b; font-size:12px;">
                @if ($categories->total() > 0)
                    Menampilkan {{ $categories->firstItem() }}-{{ $categories->lastItem() }} dari {{ $categories->total() }} kategori
                @else
                    Menampilkan 0 kategori
                @endif
            </span>
            <div class="pagination">{{ $categories->onEachSide(1)->links() }}</div>
        </div>
    </div>
@endsection
