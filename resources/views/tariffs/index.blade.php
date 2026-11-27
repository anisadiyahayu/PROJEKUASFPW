@extends('layouts.simship')

@section('title', 'Master Tarif')
@section('crumb', 'Master Tarif')

@section('content')
    @if (session('status'))
        <div role="status" style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:6px; padding:10px 12px; color:#166534; margin-bottom:16px;">
            {{ session('status') }}
        </div>
    @endif
    @if (session('error'))
        <div role="alert" style="background:#fef2f2; border:1px solid #fecaca; border-radius:6px; padding:10px 12px; color:#991b1b; margin-bottom:16px;">
            {{ session('error') }}
        </div>
    @endif

    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;">
        <div>
            <h1>Master Tarif</h1>
            <p class="subtitle">Tarif bersifat historis dan tidak dapat diedit. Perubahan harga dicatat sebagai tarif baru.</p>
        </div>
        <a href="{{ route('admin.tariffs.create') }}" class="btn btn-primary">Tambah Tarif Baru</a>
    </div>

    <div class="card" style="padding:0; overflow:hidden;">
        <table>
            <thead>
                <tr>
                    <th>MATERIAL / SPESIFIKASI</th>
                    <th>SATUAN</th>
                    <th>HARGA</th>
                    <th>BERLAKU MULAI</th>
                    <th>DIBUAT OLEH</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tariffs as $tariff)
                    <tr>
                        <td>
                            <strong>{{ $tariff->materialSpec?->name ?? 'Material tidak tersedia' }}</strong>
                            @if ($tariff->materialSpec?->trashed())
                                <span class="badge">Material dihapus</span>
                            @endif
                            @if ($tariff->materialSpec?->workType?->trashed())
                                <span class="badge">Jenis pekerjaan dihapus</span>
                            @endif
                        </td>
                        <td>{{ $tariff->unit?->name ?? 'Satuan tidak tersedia' }}</td>
                        <td>Rp {{ number_format((float) $tariff->price, 2, ',', '.') }}</td>
                        <td>{{ $tariff->effective_from?->format('d/m/Y') }}</td>
                        <td>{{ $tariff->creator?->name ?? 'Pengguna tidak tersedia' }}</td>
                        <td style="white-space:nowrap;">
                            <form method="POST" action="{{ route('admin.tariffs.destroy', $tariff) }}" style="display:inline;" onsubmit="return confirm('Hapus tarif ini? Tarif yang sudah digunakan transaksi tidak dapat dihapus.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-light">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center; color:#94a3b8;">Belum ada tarif.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 20px; border-top:1px solid #e2e8f0;">
            <span style="color:#64748b; font-size:12px;">
                @if ($tariffs->total() > 0)
                    Menampilkan {{ $tariffs->firstItem() }}-{{ $tariffs->lastItem() }} dari {{ $tariffs->total() }} tarif
                @else
                    Menampilkan 0 tarif
                @endif
            </span>
            <div class="pagination">{{ $tariffs->onEachSide(1)->links() }}</div>
        </div>
    </div>
@endsection
