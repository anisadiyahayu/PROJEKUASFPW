@extends('layouts.simship')

@section('title', 'Jenis Pekerjaan')
@section('crumb', 'Jenis Pekerjaan')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;">
        <div>
            <h1>Jenis Pekerjaan</h1>
            <p class="subtitle">Kelola jenis pekerjaan berdasarkan kategori.</p>
        </div>
        <a href="{{ route('admin.work-types.create') }}" class="btn btn-primary">Tambah Jenis Pekerjaan</a>
    </div>

    <div class="card" style="padding:0; overflow:hidden;">
        <table>
            <thead>
                <tr>
                    <th>NAMA JENIS PEKERJAAN</th>
                    <th>KATEGORI</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($workTypes as $workType)
                    <tr>
                        <td><strong>{{ $workType->name }}</strong></td>
                        <td>{{ $workType->workCategory->name }}</td>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('admin.work-types.edit', $workType) }}" class="btn btn-light">Edit</a>
                            <form method="POST" action="{{ route('admin.work-types.destroy', $workType) }}" style="display:inline;" onsubmit="return confirm('Hapus jenis pekerjaan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-light">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="text-align:center; color:#94a3b8;">Belum ada jenis pekerjaan.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 20px; border-top:1px solid #e2e8f0;">
            <span style="color:#64748b; font-size:12px;">
                @if ($workTypes->total() > 0)
                    Menampilkan {{ $workTypes->firstItem() }}-{{ $workTypes->lastItem() }} dari {{ $workTypes->total() }} jenis pekerjaan
                @else
                    Menampilkan 0 jenis pekerjaan
                @endif
            </span>
            <div class="pagination">{{ $workTypes->onEachSide(1)->links() }}</div>
        </div>
    </div>
@endsection
