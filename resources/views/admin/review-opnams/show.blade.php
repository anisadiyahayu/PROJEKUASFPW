@extends('layouts.simship')

@section('title', 'Periksa Opname')
@section('crumb', 'Review Opname')

@section('content')
    @php
        $rupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
        $statusLabels = [
            'draft' => 'Draft',
            'menunggu_review' => 'Menunggu Review',
            'perlu_revisi' => 'Perlu Revisi',
            'menunggu_approval_direksi' => 'Menunggu Approval Direksi',
            'ditolak' => 'Ditolak',
            'disetujui' => 'Disetujui',
            'dokumen_terbit' => 'Dokumen Terbit',
        ];
        $attachments = $opnam->items->flatMap(fn ($item) => $item->attachments);
    @endphp

    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:16px; flex-wrap:wrap;">
        <div>
            <h1>{{ $opnam->opnam_number }}</h1>
            <p class="subtitle">Periksa rincian pekerjaan sebelum mengambil keputusan.</p>
        </div>
        <div style="display:flex; gap:8px;">
            <a href="{{ route('admin.review-opnams.revisi', $opnam) }}" class="btn btn-primary">Revisi Data Opnam</a>
            <a href="{{ route('admin.review-opnams.index') }}" class="btn btn-light">Kembali</a>
        </div>
    </div>

    <div class="card">
        <h3 style="margin:0 0 12px;">Informasi Opnam</h3>
        <div class="grid">
            <div><label>Nama Kapal</label><div>{{ $opnam->ship?->name ?? '-' }}</div></div>
            <div><label>Nomor Lambung</label><div>{{ $opnam->ship?->hull_number ?? '-' }}</div></div>
            <div><label>Pemilik</label><div>{{ $opnam->ship?->owner?->name ?? '-' }}</div></div>
            <div><label>Jenis Kapal</label><div>{{ $opnam->jenis_kapal ?? '-' }}</div></div>
            <div><label>Lokasi Pekerjaan</label><div>{{ $opnam->items->first()?->lokasi ?? '-' }}</div></div>
            <div><label>Tanggal Opname</label><div>{{ $opnam->tanggal_opnam?->format('d M Y') ?? '-' }}</div></div>
            <div><label>Operator</label><div>{{ $opnam->creator?->name ?? '-' }}</div></div>
            <div><label>Status</label><div><span class="badge badge-{{ $opnam->status }}">{{ $statusLabels[$opnam->status] ?? $opnam->status }}</span></div></div>
        </div>
        @if ($opnam->catatan)
            <div style="margin-top:12px;"><label>Catatan Opnam</label><div>{{ $opnam->catatan }}</div></div>
        @endif
    </div>

    <div class="card">
        <h3 style="margin:0 0 12px;">Rincian Pekerjaan</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>PEKERJAAN</th>
                        <th>LOKASI</th>
                        <th>VOLUME</th>
                        <th>HARGA SATUAN</th>
                        <th>FAKTOR KETEBALAN</th>
                        <th>FAKTOR KOMPLEKSITAS</th>
                        <th>SUBTOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($opnam->items as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->description ?: '-' }}</strong>
                                <br><small class="muted">{{ $item->tariff?->materialSpec?->name ?? '-' }}</small>
                            </td>
                            <td>{{ $item->lokasi ?: '-' }}</td>
                            <td class="nowrap">{{ $item->volume }} {{ $item->tariff?->unit?->name ?? '' }}</td>
                            <td class="nowrap">{{ $rupiah($item->unit_price) }}</td>
                            <td class="nowrap">{{ $item->faktor_ketebalan ?? 1 }}x</td>
                            <td class="nowrap">{{ $item->faktor_kompleksitas ?? 1 }}x</td>
                            <td class="nowrap">{{ $rupiah($item->subtotal) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty">Belum ada item pekerjaan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="text-align:right; padding:12px 8px; background:#f8fafc;">
            Total Estimasi Biaya <strong style="color:#1e3a8a;">{{ $rupiah($opnam->total_biaya) }}</strong>
        </div>
    </div>

    <div class="card">
        <h3 style="margin:0 0 12px;">Bukti Pekerjaan</h3>
        @forelse ($attachments as $attachment)
            <div style="padding:4px 0; font-size:12px;">{{ $attachment->original_name }}</div>
        @empty
            <p class="muted">Tidak ada bukti pekerjaan.</p>
        @endforelse
    </div>

    <div class="card">
        <h3 style="margin:0 0 12px;">Keputusan Review</h3>
        <p class="muted" style="margin:0 0 14px;">Pemeriksaan telah dilakukan. Pilih satu tindakan berikut.</p>
        <div class="two-col">
            <form method="POST" action="{{ route('admin.review-opnams.approve', $opnam) }}" class="stack">
                @csrf
                <div><strong>Teruskan ke Direksi</strong></div>
                <p class="muted" style="margin:0;">Status opnam berubah menjadi menunggu approval direksi dan tagihan dibuat otomatis.</p>
                <button type="submit" class="btn btn-primary">Teruskan ke Direksi</button>
            </form>
            <form method="POST" action="{{ route('admin.review-opnams.return-for-revision', $opnam) }}" class="stack">
                @csrf
                <div>
                    <label for="catatan">Catatan Pengembalian</label>
                    <textarea id="catatan" name="catatan" rows="3" required>{{ old('catatan') }}</textarea>
                    @error('catatan') <div class="error">{{ $message }}</div> @enderror
                </div>
                <button type="submit" class="btn btn-light">Kembalikan untuk Revisi</button>
            </form>
        </div>
    </div>
@endsection
