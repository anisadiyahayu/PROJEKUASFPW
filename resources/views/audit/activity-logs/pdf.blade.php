<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Aktivitas SIMSHIP</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: "DejaVu Sans", sans-serif; font-size: 9px; color: #1e293b; margin: 16px; }
        h1 { font-size: 15px; margin: 0 0 4px; text-align: center; color: #1e3a8a; }
        .meta { text-align: center; font-size: 8px; color: #475569; margin-bottom: 4px; }
        .filter { text-align: center; font-size: 8px; color: #475569; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #94a3b8; padding: 4px 5px; vertical-align: top; word-wrap: break-word; }
        th { background: #e2e8f0; font-size: 8px; text-transform: uppercase; letter-spacing: .03em; }
        .empty { text-align: center; color: #64748b; padding: 14px; }
    </style>
</head>
<body>
    <h1>RIWAYAT AKTIVITAS SIMSHIP</h1>
    <p class="meta">
        Diekspor oleh {{ $user->name }} ({{ ucfirst($user->role) }})
        &middot; Dibuat {{ $generatedAt->format('d/m/Y H:i') }}
    </p>
    <p class="filter">
        Filter:
        @php
            $bagian = [];
            if ($filters['q'] !== '') { $bagian[] = 'Pencarian "'.$filters['q'].'"'; }
            if ($filters['event'] !== '') { $bagian[] = 'Aksi '.$eventLabels[$filters['event']]; }
            if ($filters['subject_type'] !== '') { $bagian[] = 'Modul '.\App\Support\Audit\ActivitySubject::label($filters['subject_type']); }
            if ($filterUser) { $bagian[] = 'Pengguna '.$filterUser->name; }
            if ($filters['dari'] !== null) { $bagian[] = 'Dari '.$filters['dari']; }
            if ($filters['sampai'] !== null) { $bagian[] = 'Sampai '.$filters['sampai']; }
        @endphp
        {{ $bagian === [] ? 'Semua aktivitas' : implode(' • ', $bagian) }}
    </p>

    <table>
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 12%;">Waktu</th>
                <th style="width: 12%;">Pengguna</th>
                <th style="width: 9%;">Role</th>
                <th style="width: 13%;">Aktivitas</th>
                <th style="width: 13%;">Modul</th>
                <th style="width: 37%;">Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($logs as $log)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $log->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td>{{ $log->user?->name ?? 'Sistem' }}</td>
                    <td>{{ $log->user?->role ? ucfirst($log->user->role) : '-' }}</td>
                    <td>{{ $eventLabels[$log->event] ?? $log->event }}</td>
                    <td>{{ \App\Support\Audit\ActivitySubject::label($log->subject_type) }}</td>
                    <td>{{ $log->description }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty">Tidak ada aktivitas sesuai filter yang dipilih.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
