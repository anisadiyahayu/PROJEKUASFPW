@extends('layouts.simship')

@section('title', 'Periksa Tagihan')
@section('crumb', 'Tagihan')

@section('content')
    @php
        $rupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
        $adjustment = $billing->adjustments->first();
    @endphp

    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:16px; flex-wrap:wrap;">
        <div>
            <h1>{{ $billing->billing_number ?? 'Tagihan' }}</h1>
            <p class="subtitle">Periksa rincian dan tentukan keputusan.</p>
        </div>
        <div style="display:flex; gap:8px;">
            <a href="{{ route('direksi.tagihans.revisi-opnam', $billing) }}" class="btn btn-primary">Revisi Data Opnam</a>
            <a href="{{ route('direksi.tagihans.index') }}" class="btn btn-light">Kembali</a>
        </div>
    </div>

    <div class="card">
        <h3 style="margin:0 0 12px;">Ringkasan Tagihan</h3>
        <div class="grid">
            <div><label>Nomor Opname</label><div>{{ $billing->opnam?->opnam_number ?? '-' }}</div></div>
            <div><label>Nama Kapal</label><div>{{ $billing->opnam?->ship?->name ?? '-' }}</div></div>
            <div><label>Nomor Lambung</label><div>{{ $billing->opnam?->ship?->hull_number ?? '-' }}</div></div>
            <div><label>Pemilik</label><div>{{ $billing->opnam?->ship?->owner?->name ?? '-' }}</div></div>
            <div><label>Tanggal Opname</label><div>{{ $billing->opnam?->tanggal_opnam?->format('d M Y') ?? '-' }}</div></div>
            <div><label>Status</label><div><span class="badge badge-{{ $billing->status }}">Menunggu Approval</span></div></div>
        </div>
    </div>

    <div class="card">
        <h3 style="margin:0 0 12px;">Rincian Pekerjaan</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>PEKERJAAN</th>
                        <th>VOLUME</th>
                        <th>HARGA SATUAN</th>
                        <th>SUBTOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($billing->opnam?->items ?? collect() as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->description ?: '-' }}</strong>
                                <br><small class="muted">{{ $item->tariff?->materialSpec?->name ?? '-' }}</small>
                            </td>
                            <td class="nowrap">{{ $item->volume }} {{ $item->tariff?->unit?->name ?? '' }}</td>
                            <td class="nowrap">{{ $rupiah($item->unit_price) }}</td>
                            <td class="nowrap">{{ $rupiah($item->subtotal) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty">Belum ada item pekerjaan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h3 style="margin:0 0 12px;">Penyesuaian</h3>
        @if ($adjustment)
            <div class="info" style="margin:0 0 14px;">
                {{ $adjustment->adjustment_type === App\Models\BillingAdjustment::TYPE_DISKON ? 'Diskon' : 'Markup' }}
                {{ $rupiah($adjustment->adjustment_amount) }} oleh {{ $adjustment->creator?->name ?? '-' }}.
                Catatan: {{ $adjustment->disposition_note }}
            </div>
        @else
            <p class="muted" style="margin:0 0 14px;">Belum ada penyesuaian pada tagihan ini.</p>
        @endif

        <form method="POST" action="{{ route('direksi.tagihans.apply-adjustment', $billing) }}" class="stack">
            @csrf
            <div class="two-col">
                <div>
                    <label for="adjustment_type">Jenis Penyesuaian</label>
                    <select id="adjustment_type" name="adjustment_type" required>
                        <option value="">Pilih Jenis</option>
                        <option value="diskon" @selected(old('adjustment_type') === 'diskon')>Diskon</option>
                        <option value="markup" @selected(old('adjustment_type') === 'markup')>Markup</option>
                    </select>
                    @error('adjustment_type') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label for="adjustment_value">Nominal</label>
                    <input id="adjustment_value" type="number" name="adjustment_value" min="0" step="0.01" value="{{ old('adjustment_value') }}" required>
                    @error('adjustment_value') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>
            <div>
                <label for="disposition_note">Catatan Disposisi</label>
                <textarea id="disposition_note" name="disposition_note" rows="2" required>{{ old('disposition_note') }}</textarea>
                @error('disposition_note') <div class="error">{{ $message }}</div> @enderror
            </div>
            <div><button type="submit" class="btn btn-light">Simpan Penyesuaian</button></div>
        </form>
    </div>

    <div class="card">
        <h3 style="margin:0 0 12px;">Nominal Tagihan</h3>
        <div class="summary">
            <div class="row"><span>Subtotal</span><span>{{ $rupiah($billing->subtotal_amount) }}</span></div>
            <div class="row"><span>Penyesuaian</span><span>{{ $rupiah($billing->adjustment_amount) }}</span></div>
            <div class="row total"><span>Total</span><span>{{ $rupiah($billing->total_amount) }}</span></div>
        </div>
    </div>

    <div class="card">
        <h3 style="margin:0 0 12px;">Keputusan</h3>
        <div class="two-col">
            <form method="POST" action="{{ route('direksi.tagihans.approve', $billing) }}" class="stack">
                @csrf
                <div><strong>Setujui Tagihan</strong></div>
                <p class="muted" style="margin:0;">Invoice dan technical report dibuat sebagai draf.</p>
                <button type="submit" class="btn btn-primary">Setujui Tagihan</button>
            </form>
            <form method="POST" action="{{ route('direksi.tagihans.reject', $billing) }}" class="stack">
                @csrf
                <div>
                    <label for="alasan">Alasan Penolakan</label>
                    <textarea id="alasan" name="alasan" rows="3" required>{{ old('alasan') }}</textarea>
                    @error('alasan') <div class="error">{{ $message }}</div> @enderror
                </div>
                <button type="submit" class="btn btn-light">Tolak Tagihan</button>
                <p class="muted" style="margin:0;">Penolakan bersifat final dan tidak dapat diajukan ulang.</p>
            </form>
        </div>
    </div>
@endsection
