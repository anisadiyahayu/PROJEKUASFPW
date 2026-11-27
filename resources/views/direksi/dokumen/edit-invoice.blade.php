@extends('layouts.simship')

@section('title', 'Revisi Invoice')
@section('crumb', 'Dokumen')

@section('content')
    @php $rupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.'); @endphp

    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:16px; flex-wrap:wrap;">
        <div>
            <h1>Revisi {{ $invoice->invoice_number }}</h1>
            <p class="subtitle">Ubah data teks snapshot dan tanggal invoice sebelum dokumen terbit.</p>
        </div>
        <a href="{{ route('direksi.dokumen.index') }}" class="btn btn-light">Kembali</a>
    </div>

    <form method="POST" action="{{ route('direksi.dokumen.update-invoice', $invoice) }}" class="stack">
        @csrf
        @method('PUT')

        <div class="card">
            <h3 style="margin:0 0 12px;">Data Invoice</h3>
            <div class="grid">
                <div>
                    <label for="ship_name">Nama Kapal</label>
                    <input id="ship_name" type="text" name="ship_name" value="{{ old('ship_name', $invoice->ship_name) }}" required>
                    @error('ship_name') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label for="hull_number">Nomor Lambung</label>
                    <input id="hull_number" type="text" name="hull_number" value="{{ old('hull_number', $invoice->hull_number) }}" required>
                    @error('hull_number') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label for="owner_name">Nama Pemilik</label>
                    <input id="owner_name" type="text" name="owner_name" value="{{ old('owner_name', $invoice->owner_name) }}" required>
                    @error('owner_name') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label for="issue_date">Tanggal Invoice</label>
                    <input id="issue_date" type="date" name="issue_date" value="{{ old('issue_date', $invoice->issue_date?->format('Y-m-d')) }}" required>
                    @error('issue_date') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div style="grid-column:1 / -1;">
                    <label for="owner_address">Alamat Pemilik</label>
                    <textarea id="owner_address" name="owner_address" rows="2">{{ old('owner_address', $invoice->owner_address) }}</textarea>
                    @error('owner_address') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="card">
            <h3 style="margin:0 0 12px;">Nominal</h3>
            <div class="summary">
                <div class="row"><span>Subtotal</span><span>{{ $rupiah($invoice->subtotal_amount) }}</span></div>
                <div class="row"><span>Penyesuaian</span><span>{{ $rupiah($invoice->adjustment_amount) }}</span></div>
                <div class="row total"><span>Total</span><span>{{ $rupiah($invoice->total_amount) }}</span></div>
            </div>
            <p class="muted" style="margin:12px 0 0;">Nominal berasal dari tagihan dan tidak diubah lewat revisi ini.</p>
        </div>

        <div>
            <button type="submit" class="btn btn-primary">Simpan Revisi</button>
        </div>
    </form>
@endsection
