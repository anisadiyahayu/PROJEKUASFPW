<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMSHIP - @yield('title')</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 0; background: #f4f6fa; color: #1f2937; }
<<<<<<< HEAD
        header { display: flex; justify-content: space-between; align-items: center; gap: 18px; padding: 16px 24px; background: #1e3a8a; color: #fff; flex-wrap: wrap; }
        nav { display: flex; gap: 6px; flex-wrap: wrap; }
        nav a { color: #dbeafe; padding: 6px 10px; border-radius: 6px; text-decoration: none; font-size: 13px; }
        nav a:hover, nav a.active { background: rgba(255, 255, 255, .16); color: #fff; }
=======
        header { display: flex; justify-content: space-between; align-items: center; padding: 16px 24px; background: #1e3a8a; color: #fff; }
>>>>>>> d84cf2129cb7286d690c39c169d2bc78fcf194c3
        main { padding: 24px; }
        button { background: #1e3a8a; color: #fff; border: 0; padding: 8px 14px; border-radius: 6px; cursor: pointer; }
        .card { background: #fff; padding: 24px; border-radius: 10px; max-width: 480px; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
    </style>
</head>
<body>
<<<<<<< HEAD
    @auth
        @php
            $links = match (auth()->user()->role) {
                'petugas' => [
                    'Dashboard' => route('petugas.dashboard'),
                    'Buat Opname' => route('petugas.opnams.create'),
                    'Opname Saya' => route('petugas.opnams.index'),
                    'Riwayat Aktivitas' => route('activity-logs.index'),
                ],
                'admin' => [
                    'Dashboard' => route('admin.dashboard'),
                    'Pemilik' => route('admin.owners.index'),
                    'Kapal' => route('admin.ships.index'),
                    'Data Opname' => route('admin.opnams.index'),
                    'Rekapitulasi' => route('reports.recapitulation.index'),
                    'Riwayat Perbaikan' => route('repair-history.index'),
                    'Riwayat Aktivitas' => route('activity-logs.index'),
                ],
                default => [
                    'Dashboard' => route('direksi.dashboard'),
                    'Transaksi Disetujui' => route('direksi.approved.index'),
                    'Riwayat Perbaikan' => route('repair-history.index'),
                    'Rekapitulasi' => route('reports.recapitulation.index'),
                    'Riwayat Aktivitas' => route('activity-logs.index'),
                ],
            };
        @endphp
    @endauth
    <header>
        <strong>SIMSHIP</strong>
        @auth
            <nav>
                @foreach ($links as $label => $url)
                    <a href="{{ $url }}">{{ $label }}</a>
                @endforeach
            </nav>
        @endauth
        <div>
            @auth
                {{ auth()->user()->name }} ({{ ucfirst(auth()->user()->role) }})
                <form action="{{ route('logout') }}" method="POST" style="display:inline">
                    @csrf
                    <button type="submit">Logout</button>
                </form>
            @endauth
=======
    <header>
        <strong>SIMSHIP</strong>
        <div>
            {{ auth()->user()->name }} ({{ ucfirst(auth()->user()->role) }})
            <form action="{{ route('logout') }}" method="POST" style="display:inline">
                @csrf
                <button type="submit">Logout</button>
            </form>
>>>>>>> d84cf2129cb7286d690c39c169d2bc78fcf194c3
        </div>
    </header>
    <main>
        @yield('content')
    </main>
</body>
<<<<<<< HEAD
</html>
=======
</html>
>>>>>>> d84cf2129cb7286d690c39c169d2bc78fcf194c3
