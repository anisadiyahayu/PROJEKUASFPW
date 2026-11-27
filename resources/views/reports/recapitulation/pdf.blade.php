<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Pekerjaan Kapal</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: "DejaVu Sans", sans-serif; font-size: 10px; color: #1e293b; margin: 18px; }
        h1 { font-size: 15px; margin: 0 0 4px; text-align: center; color: #1e3a8a; }
        .meta { text-align: center; font-size: 9px; color: #475569; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #94a3b8; padding: 4px 5px; vertical-align: top; }
        th { background: #e2e8f0; font-size: 8px; text-transform: uppercase; letter-spacing: .03em; }
        td.number { text-align: right; white-space: nowrap; }
        tfoot td { font-weight: bold; background: #f1f5f9; }
        .empty { text-align: center; color: #64748b; padding: 14px; }
    </style>
</head>
<body>
    <h1>REKAPITULASI PEKERJAAN KAPAL</h1>
    <p class="meta">
        Periode {{ \Illuminate\Support\Carbon::parse($filters['dari'])->format('d/m/Y') }}
        s.d. {{ \Illuminate\Support\Carbon::parse($filters['sampai'])->format('d/m/Y') }}
        &middot; Dibuat {{ $generatedAt->format('d/m/Y H:i') }}
    </p>

    <table>
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 12%;">Nomor Opnam</th>
                <th style="width: 8%;">Tanggal</th>
                <th style="width: 16%;">Nama Kapal</th>
                <th style="width: 10%;">Nomor Lambung</th>
                <th style="width: 16%;">Pemilik</th>
                <th style="width: 11%;">Status</th>
                <th style="width: 6%;">Jumlah Item</th>
                <th style="width: 12%;">Total Biaya (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $opnam)
                <tr>
                    <td class="number">{{ $loop->iteration }}</td>
                    <td>{{ $opnam->opnam_number }}</td>
                    <td>{{ $opnam->tanggal_opnam?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $opnam->ship?->name ?? '-' }}</td>
                    <td>{{ $opnam->ship?->hull_number ?? '-' }}</td>
                    <td>{{ $opnam->ship?->owner?->name ?? '-' }}</td>
                    <td>{{ \App\Support\OpnamStatus::label($opnam->status) }}</td>
                    <td class="number">{{ $opnam->items_count }}</td>
                    <td class="number">{{ number_format((float) $opnam->total_biaya, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="empty">Tidak ada opnam pada periode dan filter yang dipilih.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7">TOTAL</td>
                <td class="number">{{ number_format($summary['total_item'], 0, ',', '.') }}</td>
                <td class="number">{{ number_format($summary['total_biaya'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
