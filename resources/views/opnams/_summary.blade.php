@php
    $items = $draft['items'] ?? [];
    $baseSubtotal = collect($items)->sum('subtotal');
    $faktorTambahan = collect($items)->sum('adjustment');
    $totalEstimasi = $baseSubtotal + $faktorTambahan;
    $rupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
@endphp
<div class="card">
    <h3 style="margin: 0 0 4px; font-size: 16px;">Estimasi Biaya</h3>
    <p style="color: #64748b; font-size: 12px; margin: 0 0 12px;">Ringkasan perhitungan sementara</p>
    <div class="summary">
        <div class="row"><span>Item pekerjaan</span><span>{{ count($items) }} item</span></div>
        <div class="row"><span>Subtotal</span><span id="subtotal">{{ $rupiah($baseSubtotal) }}</span></div>
        <div class="row"><span>Faktor tambahan</span><span id="faktor-tambahan">{{ $rupiah($faktorTambahan) }}</span></div>
        <div style="border-top: 1px solid #e2e8f0; margin-top: 10px; padding-top: 10px;">
            <div style="color: #64748b; font-size: 11px;">Total Estimasi</div>
            <div class="total" id="total" style="border-top: 0; margin-top: 2px; padding-top: 0;">{{ $rupiah($totalEstimasi) }}</div>
            <div style="color: #64748b; font-size: 11px; margin-top: 4px;">Termasuk faktor kompleksitas pekerjaan</div>
        </div>
    </div>
</div>
