@extends('layouts.simship')

@section('title', 'Master Satuan')
@section('crumb', 'Master Satuan')

@section('content')
    @if (session('error'))
        <div role="alert" style="background:#fef2f2; border:1px solid #fecaca; border-radius:6px; padding:10px 12px; color:#991b1b; margin-bottom:16px;">
            {{ session('error') }}
        </div>
    @endif

    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;">
        <div>
            <h1>Master Satuan</h1>
            <p class="subtitle">Kelola satuan yang digunakan pada tarif pekerjaan.</p>
        </div>
        <a href="{{ route('admin.units.create') }}" class="btn btn-primary">Tambah Satuan</a>
    </div>

    <div class="card" style="padding:0; overflow:hidden;">
        <table>
            <thead>
                <tr>
                    <th>KODE</th>
                    <th>NAMA SATUAN</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($units as $unit)
                    <tr>
                        <td><strong>{{ $unit->code }}</strong></td>
                        <td>{{ $unit->name }}</td>
                        <td style="white-space:nowrap;">
                            <a href="{{ route('admin.units.edit', $unit) }}" class="btn btn-light">Edit</a>
                            <form method="POST" action="{{ route('admin.units.destroy', $unit) }}" style="display:inline;" onsubmit="return confirm('Hapus satuan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-light">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="text-align:center; color:#94a3b8;">Belum ada satuan.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 20px; border-top:1px solid #e2e8f0;">
            <span style="color:#64748b; font-size:12px;">
                @if ($units->total() > 0)
                    Menampilkan {{ $units->firstItem() }}-{{ $units->lastItem() }} dari {{ $units->total() }} satuan
                @else
                    Menampilkan 0 satuan
                @endif
            </span>
            <div class="pagination">{{ $units->onEachSide(1)->links() }}</div>
        </div>
    </div>
@endsection
