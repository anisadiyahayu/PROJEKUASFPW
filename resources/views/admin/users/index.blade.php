@extends('layouts.simship')

@section('title', 'Manajemen Pengguna')
@section('crumb', 'Manajemen Pengguna')

@section('content')
    @if (session('status'))
        <div class="info" style="margin-bottom:16px;">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="alert-error">{{ session('error') }}</div>
    @endif

    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;">
        <div>
            <h1>Manajemen Pengguna</h1>
            <p class="subtitle">Kelola akun Petugas, Admin, dan Direksi.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Tambah Pengguna</a>
    </div>

    <div class="card" style="padding:0; overflow:hidden;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>NAMA</th>
                        <th>USERNAME</th>
                        <th>EMAIL</th>
                        <th>ROLE</th>
                        <th>STATUS</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td><strong>{{ $user->name }}</strong></td>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->role }}</td>
                            <td>
                                <span class="badge {{ $user->is_active ? 'badge-disetujui' : 'badge-ditolak' }}">
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td style="white-space:nowrap;">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-light">Edit</a>
                                @if ($user->is(auth()->user()))
                                    <span class="muted" title="Tidak dapat menonaktifkan akun sendiri">Akun sendiri</span>
                                @elseif ($user->is_active)
                                    <form method="POST" action="{{ route('admin.users.deactivate', $user) }}" style="display:inline;" onsubmit="return confirm('Nonaktifkan akun ini?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-light">Nonaktifkan</button>
                                    </form>
                                @else
                                    <span class="muted">Sudah nonaktif</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty">Belum ada pengguna.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:12px 16px;">{{ $users->links() }}</div>
    </div>
@endsection
