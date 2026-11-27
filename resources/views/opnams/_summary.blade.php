@php
    $items = $draft['items'] ?? [];
    $baseSubtotal = collect($items)->sum('subtotal');
    $adjustment = (float) ($draft['adjustment_amount'] ?? 0);
    $totalEstimasi = round($baseSubtotal + $adjustment, 2);

    $itemPertama = collect($items)->first();
    if ($itemPertama && (array_key_exists('faktor_ketebalan', $itemPertama) || array_key_exists('faktor_kompleksitas', $itemPertama))) {
        $faktorAwal = (float) ($itemPertama['faktor_ketebalan'] ?? 1) * (float) ($itemPertama['faktor_kompleksitas'] ?? 1);
    } else {
        $faktorAwal = (float) ($itemPertama['faktor'] ?? 1);
    }
    $faktorLabel = rtrim(rtrim(number_format($faktorAwal, 2, '.', ''), '0'), '.').'x';
    $rupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
@endphp
<div class="card">
    <h3 style="margin: 0 0 4px; font-size: 16px;">Estimasi Biaya</h3>
    <p style="color: #64748b; font-size: 12px; margin: 0 0 12px;">Ringkasan perhitungan sementara</p>
    <div class="summary">
        <div class="row"><span>Item pekerjaan</span><span id="item-count">{{ count($items) }} item</span></div>
        <div class="row"><span>Subtotal</span><span id="subtotal">{{ $rupiah($baseSubtotal) }}</span></div>
        <div class="row"><span>Faktor</span><span id="faktor-tambahan">{{ $faktorLabel }}</span></div>
        @if ($adjustment !== 0.0)
            <div class="row"><span>Penyesuaian</span><span id="adjustment">{{ $rupiah($adjustment) }}</span></div>
        @endif
        <div style="border-top: 1px solid #e2e8f0; margin-top: 10px; padding-top: 10px;">
            <div style="color: #64748b; font-size: 11px;">Total Estimasi</div>
            <div class="total" id="total" style="border-top: 0; margin-top: 2px; padding-top: 0;">{{ $rupiah($totalEstimasi) }}</div>
            <div style="color: #64748b; font-size: 11px; margin-top: 4px;">Termasuk faktor ketebalan dan kompleksitas</div>
        </div>
    </div>
</div>
