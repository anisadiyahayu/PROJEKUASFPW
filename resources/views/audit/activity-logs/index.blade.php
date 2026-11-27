@extends('layouts.simship')

@section('title', 'Riwayat Aktivitas')
@section('crumb', 'Riwayat Aktivitas')

@section('content')
    @php
        $eventLabels = [
            'created' => 'Dibuat',
            'updated' => 'Diperbarui',
            'deleted' => 'Dihapus',
            'restored' => 'Dipulihkan',
        ];
    @endphp

    <h1>Riwayat Aktivitas</h1>
    <p class="subtitle">
        @if ($isPetugas)
            Aktivitas yang Anda lakukan pada data SIMSHIP.
        @else
            Seluruh aktivitas perubahan data yang tercatat pada sistem.
        @endif
    </p>

    <div class="card">
        <form method="GET" action="{{ route('activity-logs.index') }}" class="filters">
            <div style="flex: 1; min-width: 200px;">
                <label for="q">Pencarian</label>
                <input id="q" type="text" name="q" value="{{ $filters['q'] }}" placeholder="Deskripsi atau jenis aksi...">
            </div>
            <div>
                <label for="event">Aksi</label>
                <select id="event" name="event">
                    <option value="">Semua Aksi</option>
                    @foreach ($events as $event)
                        <option value="{{ $event }}" @selected($filters['event'] === $event)>{{ $eventLabels[$event] ?? $event }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width: 180px;">
                <label for="subject_type">Subjek</label>
                <select id="subject_type" name="subject_type">
                    <option value="">Semua Subjek</option>
                    @foreach ($subjects as $type => $label)
                        <option value="{{ $type }}" @selected($filters['subject_type'] === $type)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @unless ($isPetugas)
                <div style="min-width: 180px;">
                    <label for="user_id">Pengguna</label>
                    <select id="user_id" name="user_id">
                        <option value="">Semua Pengguna</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected($filters['user_id'] === $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endunless
            <div>
                <label for="dari">Dari</label>
                <input id="dari" type="date" name="dari" value="{{ $filters['dari'] }}">
            </div>
            <div>
                <label for="sampai">Sampai</label>
                <input id="sampai" type="date" name="sampai" value="{{ $filters['sampai'] }}">
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
            <div>
                <a href="{{ route('activity-logs.index') }}" class="btn btn-light">Reset</a>
            </div>
        </form>
    </div>

    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>WAKTU</th>
                        <th>PENGGUNA</th>
                        <th>AKSI</th>
                        <th>SUBJEK</th>
                        <th>DESKRIPSI</th>
                        <th>IP</th>
                        <th>PERUBAHAN</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td style="white-space: nowrap;">{{ $log->created_at?->format('d M Y H:i') ?? '-' }}</td>
                            <td>{{ $log->user?->name ?? 'Sistem' }}</td>
                            <td><span class="badge">{{ $eventLabels[$log->event] ?? $log->event }}</span></td>
                            <td>{{ \App\Support\Audit\ActivitySubject::label($log->subject_type) }}</td>
                            <td>{{ $log->description }}</td>
                            <td>{{ $log->ip_address ?? '-' }}</td>
                            <td>
                                @if ($log->old_values || $log->new_values)
                                    <details class="changes">
                                        <summary>Lihat detail</summary>
                                        @if ($log->old_values)
                                            <div style="margin-top: 6px;"><strong style="font-size: 11px;">Sebelum</strong></div>
                                            <pre>{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                        @endif
                                        @if ($log->new_values)
                                            <div style="margin-top: 6px;"><strong style="font-size: 11px;">Sesudah</strong></div>
                                            <pre>{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                        @endif
                                    </details>
                                @else
                                    <span class="muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty">Belum ada aktivitas yang tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; border-top: 1px solid #e2e8f0;">
            <span class="muted" style="font-size: 12px;">
                @if ($logs->total() > 0)
                    Menampilkan {{ $logs->firstItem() }}-{{ $logs->lastItem() }} dari {{ $logs->total() }} aktivitas
                @else
                    Menampilkan 0 aktivitas
                @endif
            </span>
            <div class="pagination">{{ $logs->onEachSide(1)->links() }}</div>
        </div>
    </div>
@endsection
