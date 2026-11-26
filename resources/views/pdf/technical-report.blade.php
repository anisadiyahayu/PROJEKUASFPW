<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Teknis {{ $report->report_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .muted { color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #bbb; padding: 6px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>
    <h1>LAPORAN TEKNIS</h1>
    <div class="muted">Bayu Bahari — Sistem Pencatatan Opnam Pekerjaan Kapal</div>

    <table>
        <tr><th>Nomor laporan</th><td>{{ $report->report_number }}</td></tr>
        <tr><th>Tanggal</th><td>{{ $report->issue_date?->format('d/m/Y') }}</td></tr>
        <tr><th>Nomor tagihan</th><td>{{ $report->billing->billing_number }}</td></tr>
        <tr><th>Nama kapal</th><td>{{ $report->ship_name }}</td></tr>
        <tr><th>Nomor lambung</th><td>{{ $report->hull_number }}</td></tr>
        <tr><th>Pemilik</th><td>{{ $report->owner_name }}</td></tr>
        <tr><th>Alamat pemilik</th><td>{{ $report->owner_address ?: '-' }}</td></tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Pekerjaan</th>
                <th>Lokasi</th>
                <th>Volume</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($report->billing->opnam->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->lokasi ?: '-' }}</td>
                    <td>{{ $item->volume }}</td>
                    <td>{{ $item->catatan_tambahan ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
