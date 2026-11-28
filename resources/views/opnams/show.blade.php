@extends('layouts.simship')

@section('title', 'Detail Opname')
@section('crumb', 'Detail Opname')

@section('content')
    @php
        $statusLabels = [
            'draft' => 'Draft',
            'menunggu_review' => 'Menunggu Review',
            'perlu_revisi' => 'Perlu Revisi',
            'menunggu_approval_direksi' => 'Menunggu Approval Direksi',
            'ditolak' => 'Ditolak',
            'disetujui' => 'Disetujui',
            'dokumen_terbit' => 'Dokumen Terbit',
        ];
        $rupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
    @endphp

    <style>
        .opnam-table th { font-size: 11px; }
        .opnam-table td { font-size: 12px; line-height: 1.35; }
        .opnam-table td.angka { white-space: nowrap; }
        .opnam-total { font-size: 14px; white-space: nowrap; }
    </style>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <div>
            <h1>{{ $opnam->opnam_number }}</h1>
            <p class="subtitle">Detail opnam bersifat baca saja.</p>
        </div>
        <a href="{{ $backRoute }}" class="btn btn-light">← Kembali</a>
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
            <div style="margin-top:12px;"><label>Catatan</label><div>{{ $opnam->catatan }}</div></div>
        @endif
    </div>

    <div class="card">
        <h3 style="margin:0 0 12px;">Detail Pekerjaan</h3>
        <table class="opnam-table">
            <thead>
                <tr>
                    <th>PEKERJAAN</th><th>LOKASI</th><th>VOLUME</th><th>HARGA SATUAN</th>
                    <th>FAKTOR KETEBALAN</th><th>FAKTOR KOMPLEKSITAS</th><th>SUBTOTAL</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($opnam->items as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->description ?: '-' }}</strong>
                            <br><small style="color:#94a3b8;">{{ $item->tariff?->materialSpec?->name ?? '-' }}</small>
                        </td>
                        <td>{{ $item->lokasi ?? '-' }}</td>
                        <td class="angka">{{ $item->volume }} {{ $item->tariff?->unit?->name ?? '' }}</td>
                        <td class="angka">{{ $rupiah($item->unit_price) }}</td>
                        <td class="angka">{{ $item->faktor_ketebalan ?? 1 }}x</td>
                        <td class="angka">{{ $item->faktor_kompleksitas ?? 1 }}x</td>
                        <td class="angka">{{ $rupiah($item->subtotal) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align:center; color:#94a3b8;">Belum ada item pekerjaan.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div style="text-align:right; padding:12px 8px; background:#f8fafc;">
            Total Estimasi Biaya &nbsp; <strong class="opnam-total" style="color:#1e3a8a;">{{ $rupiah($opnam->total_biaya) }}</strong>
        </div>
    </div>

    <div class="card">
        <h3 style="margin:0 0 12px;">Bukti Pekerjaan</h3>
        @php $attachments = $opnam->items->flatMap(fn ($item) => $item->attachments); @endphp
        @forelse ($attachments as $attachment)
            <div style="padding:4px 0; font-size:12px;">• {{ $attachment->original_name }}</div>
        @empty
            <p style="color:#94a3b8;">Tidak ada bukti pekerjaan.</p>
        @endforelse
    </div>
@endsection
