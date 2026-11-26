<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .muted { color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #bbb; padding: 6px; text-align: left; }
        th { background: #eee; }
        .right { text-align: right; }
        .total { margin-top: 16px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>INVOICE</h1>
    <div class="muted">Bayu Bahari — Sistem Pencatatan Opnam Pekerjaan Kapal</div>

    <table>
        <tr><th>Nomor invoice</th><td>{{ $invoice->invoice_number }}</td></tr>
        <tr><th>Tanggal</th><td>{{ $invoice->issue_date?->format('d/m/Y') }}</td></tr>
        <tr><th>Nomor tagihan</th><td>{{ $invoice->billing->billing_number }}</td></tr>
        <tr><th>Nama kapal</th><td>{{ $invoice->ship_name }}</td></tr>
        <tr><th>Nomor lambung</th><td>{{ $invoice->hull_number }}</td></tr>
        <tr><th>Pemilik</th><td>{{ $invoice->owner_name }}</td></tr>
        <tr><th>Alamat pemilik</th><td>{{ $invoice->owner_address ?: '-' }}</td></tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Pekerjaan</th>
                <th>Lokasi</th>
                <th>Volume</th>
                <th class="right">Harga satuan</th>
                <th class="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->billing->opnam->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->lokasi ?: '-' }}</td>
                    <td>{{ $item->volume }}</td>
                    <td class="right">{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td class="right">{{ number_format((float) $item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="total">
        <tr><th>Subtotal</th><td class="right">{{ number_format((float) $invoice->subtotal_amount, 2) }}</td></tr>
        <tr><th>Penyesuaian</th><td class="right">{{ number_format((float) $invoice->adjustment_amount, 2) }}</td></tr>
        <tr><th>Total</th><td class="right">{{ number_format((float) $invoice->total_amount, 2) }}</td></tr>
    </table>
</body>
</html>
