@extends('layouts.simship')

@section('title', 'Master Material')
@section('crumb', 'Master Material')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;">
        <div>
            <h1>Master Material/Spesifikasi</h1>
            <p class="subtitle">Kelola material dan spesifikasi berdasarkan jenis pekerjaan.</p>
        </div>
        <a href="{{ route('admin.material-specs.create') }}" class="btn btn-primary">Tambah Material</a>
    </div>

    <div class="card" style="padding:0; overflow:hidden;">
        <table>
            <thead>
                <tr>
                    <th>MATERIAL / SPESIFIKASI</th>
                    <th>JENIS PEKERJAAN</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($materialSpecs as $materialSpec)
                    <tr>
                        <td><strong>{{ $materialSpec->name }}</strong></td>
                        <td>
                            {{ $materialSpec->workType?->name ?? 'Jenis pekerjaan tidak tersedia' }}
                            @if ($materialSpec->workType?->trashed())
                                <span class="badge">Jenis pekerjaan dihapus</span>
                            @endif
                        </td>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('admin.material-specs.edit', $materialSpec) }}" class="btn btn-light">Edit</a>
                            <form method="POST" action="{{ route('admin.material-specs.destroy', $materialSpec) }}" style="display:inline;" onsubmit="return confirm('Hapus material/spesifikasi ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-light">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="text-align:center; color:#94a3b8;">Belum ada material/spesifikasi.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 20px; border-top:1px solid #e2e8f0;">
            <span style="color:#64748b; font-size:12px;">
                @if ($materialSpecs->total() > 0)
                    Menampilkan {{ $materialSpecs->firstItem() }}-{{ $materialSpecs->lastItem() }} dari {{ $materialSpecs->total() }} material/spesifikasi
                @else
                    Menampilkan 0 material/spesifikasi
                @endif
            </span>
            <div class="pagination">{{ $materialSpecs->onEachSide(1)->links() }}</div>
        </div>
    </div>
@endsection
