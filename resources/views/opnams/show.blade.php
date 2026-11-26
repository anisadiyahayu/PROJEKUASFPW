@extends('layouts.simship')

@section('title', 'Detail Opname')
@section('crumb', 'Opname Saya / Detail')

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
        $firstAttachment = $record->attachments->first();
        $mime = $firstAttachment?->mime_type ?? '';
        $format = str_contains($mime, 'pdf') ? 'PDF'
            : (str_contains($mime, 'png') ? 'PNG'
            : (str_contains($mime, 'jpeg') || str_contains($mime, 'jpg') ? 'JPG' : 'FILE'));
        $tanggalUnggah = $firstAttachment?->created_at?->format('d M Y') ?? '-';
        $subtotal = $record->items->sum(fn ($item) => (float) $item->subtotal);
        $faktorAmount = $record->items->sum(fn ($item) => (float) $item->subtotal * ((float) $item->faktor - 1));
        $badgeClass = 'detail-badge '.match ($record->status) {
            'disetujui' => 'detail-badge-disetujui',
            'menunggu_review', 'perlu_revisi' => 'detail-badge-'.$record->status,
            default => 'detail-badge-default',
        };
        $adminDetail = auth()->user()->role === 'admin';
        $backRoute = $adminDetail ? route('admin.opnams.index') : route('petugas.opnams.index');
        $backLabel = $adminDetail ? 'Data Opname' : 'Opname Saya';
    @endphp

    <p style="margin:0 0 var(--space-3);">
        <a href="{{ $backRoute }}" style="color: var(--color-primary-text); font-weight:600;">&lt; Kembali ke {{ $backLabel }}</a>
    </p>

    <h1>Detail Opname</h1>
    <p class="subtitle">Informasi transaksi dan estimasi pekerjaan kapal.</p>

    <div class="detail-grid">
        <div class="detail-card">
            <div class="detail-section" style="display:flex; justify-content:space-between; align-items:flex-start; gap:var(--space-4);">
                <div>
                    <div class="detail-label">Nomor Opname</div>
                    <div class="detail-number">{{ $record->opnam_number }}</div>
                    <div class="detail-meta">
                        Dibuat oleh {{ $record->creator?->name ?? '-' }} • {{ $record->created_at?->format('d M Y') ?? '-' }}
                    </div>
                </div>
                <span class="{{ $badgeClass }}">{{ $statusLabels[$record->status] ?? $record->status }}</span>
            </div>

            <div class="detail-section">
                <div class="detail-heading">Informasi Kapal</div>
                <div class="detail-grid-2">
                    <div>
                        <span class="detail-label">Nama Kapal</span>
                        <span class="detail-value">{{ $record->ship?->name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="detail-label">Nomor Lambung</span>
                        <span class="detail-value">{{ $record->ship?->hull_number ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="detail-label">Pemilik Kapal</span>
                        <span class="detail-value">{{ $record->ship?->owner?->name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="detail-label">Lokasi Pekerjaan</span>
                        <span class="detail-value">{{ $record->lokasi ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="detail-section">
                <div class="detail-heading">Rincian Pekerjaan</div>
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th>Pekerjaan</th>
                            <th>Qty</th>
                            <th>Harga Satuan</th>
                            <th>Faktor</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($record->items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->tariff?->materialSpec?->workType?->name ?? '-' }}</strong><br>
                                    <small style="color:var(--color-muted);">{{ $item->tariff?->materialSpec?->name ?? '-' }}</small>
                                </td>
                                <td>{{ $item->volume }} {{ $item->tariff?->unit?->name ?? '' }}</td>
                                <td>{{ $rupiah($item->unit_price) }}</td>
                                <td>{{ $item->faktor }}×</td>
                                <td>{{ $rupiah((float) $item->subtotal * (float) $item->faktor) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" style="text-align:center; color:var(--color-muted);">Tidak ada item pekerjaan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="detail-table-footer">
                    Total Estimasi Biaya &nbsp;
                    <strong class="detail-total">{{ $rupiah($record->total_biaya) }}</strong>
                </div>
            </div>

            <div class="detail-section">
                <div class="detail-heading">Bukti Pekerjaan</div>
                <div style="display:flex; align-items:center; gap:var(--space-3);">
                    <span class="detail-icon-box" aria-hidden="true">📷</span>
                    <div style="flex:1;">
                        <strong>{{ $record->attachments->count() }} foto inspeksi</strong><br>
                        <span class="detail-meta">{{ $format }} • Diunggah {{ $tanggalUnggah }}</span>
                    </div>
                    @if ($firstAttachment)
                        <a class="btn-outline" href="{{ \Illuminate\Support\Facades\Storage::url($firstAttachment->file_path) }}" target="_blank" rel="noopener">👁 Lihat File</a>
                    @endif
                </div>
                @if ($record->attachments->count() > 1)
                    <div class="file-links">
                        @foreach ($record->attachments as $attachment)
                            <a href="{{ \Illuminate\Support\Facades\Storage::url($attachment->file_path) }}" target="_blank" rel="noopener">{{ $attachment->original_name }}</a>
                            @if (! $loop->last) • @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <aside class="detail-card">
            <div class="summary-heading">Ringkasan Estimasi</div>
            <div class="summary-row"><span>Subtotal pekerjaan</span><strong>{{ $rupiah($subtotal) }}</strong></div>
            <div class="summary-row"><span>Faktor kompleksitas</span><strong>{{ $rupiah($faktorAmount) }}</strong></div>
            <div class="summary-highlight">
                <div class="detail-label">Total Estimasi Biaya</div>
                <div class="detail-total">{{ $rupiah($record->total_biaya) }}</div>
            </div>
            <div class="locked-footer">
                <strong>🔒 Transaksi terkunci</strong>
                <small>Data tidak dapat diubah selama proses review.</small>
            </div>
        </aside>
    </div>
@endsection
