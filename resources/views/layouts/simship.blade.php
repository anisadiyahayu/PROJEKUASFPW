<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMSHIP - @yield('title')</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, -apple-system, sans-serif; background: #f4f7fb; color: #1e293b; font-size: 14px; }
        a { color: inherit; text-decoration: none; }
        .app { display: flex; min-height: 100vh; }
        .sidebar { width: 232px; flex: 0 0 232px; background: #fff; border-right: 1px solid #e2e8f0; display: flex; flex-direction: column; }
        .brand { padding: 20px 18px; border-bottom: 1px solid #e2e8f0; }
        .brand strong { display: block; color: #1e3a8a; font-size: 17px; letter-spacing: .04em; }
        .brand small { color: #94a3b8; font-size: 11px; }
        .nav { padding: 18px 12px; flex: 1; }
        .nav-label { color: #94a3b8; font-size: 11px; letter-spacing: .08em; margin: 0 8px 10px; text-transform: uppercase; }
        .nav a, .nav span.item { display: flex; align-items: center; gap: 10px; padding: 9px 10px; border-radius: 6px; margin-bottom: 3px; color: #475569; }
        .nav a:hover { background: #f1f5f9; }
        .nav .active { background: #eff6ff; color: #1d4ed8; font-weight: 600; border-left: 3px solid #1d4ed8; padding-left: 7px; }
        .nav span.item { color: #94a3b8; cursor: default; }
        .nav .divider { height: 1px; background: #e2e8f0; margin: 16px 8px; }
        .profile { border-top: 1px solid #e2e8f0; padding: 14px 16px; display: flex; align-items: center; gap: 10px; }
        .avatar { width: 34px; height: 34px; border-radius: 50%; background: #1e3a8a; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; }
        .profile small { display: block; color: #94a3b8; }
        .main { flex: 1; min-width: 0; }
        .topbar { height: 66px; background: #fff; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; padding: 0 24px; }
        .crumbs { color: #64748b; font-size: 13px; }
        .crumbs strong { color: #0f172a; }
        .topbar-user { display: flex; align-items: center; gap: 12px; }
        .topbar-user small { display: block; color: #94a3b8; font-size: 11px; }
        .content { padding: 24px; }
        h1 { font-size: 26px; margin: 0 0 6px; }
        .subtitle { color: #64748b; margin: 0 0 20px; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin-bottom: 16px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; border: 0; border-radius: 6px; padding: 9px 16px; cursor: pointer; font-size: 13px; font-weight: 600; }
        .btn-primary { background: #1e3a8a; color: #fff; }
        .btn-light { background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; }
        .btn:disabled { opacity: .5; cursor: not-allowed; }
        input[type=text], input[type=date], input[type=number], input[type=file], select, textarea { width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font: inherit; background: #fff; }
        label { display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; color: #64748b; font-size: 11px; letter-spacing: .04em; padding: 10px 8px; border-bottom: 1px solid #e2e8f0; }
        td { padding: 12px 8px; border-bottom: 1px solid #f1f5f9; }
        .badge { display: inline-block; padding: 3px 9px; border-radius: 999px; font-size: 11px; background: #f1f5f9; color: #475569; }
        .badge-draft { background: #f1f5f9; color: #475569; }
        .badge-menunggu_review { background: #fef3c7; color: #92400e; }
        .badge-perlu_revisi { background: #fee2e2; color: #991b1b; }
        .badge-menunggu_approval_direksi { background: #dbeafe; color: #1e40af; }
        .badge-ditolak { background: #fee2e2; color: #991b1b; }
        .badge-disetujui { background: #dcfce7; color: #166534; }
        .badge-dokumen_terbit { background: #ede9fe; color: #5b21b6; }
        .summary { background: #f8fafc; border-radius: 8px; padding: 14px; }
        .summary .row { display: flex; justify-content: space-between; padding: 4px 0; color: #64748b; }
        .summary .total { border-top: 1px solid #e2e8f0; margin-top: 8px; padding-top: 10px; font-weight: 700; color: #1e3a8a; font-size: 18px; }
        .steps { display: flex; gap: 8px; margin-bottom: 16px; }
        .step { flex: 1; display: flex; align-items: center; gap: 8px; padding: 10px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; color: #94a3b8; font-size: 12px; }
        .step .num { width: 22px; height: 22px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-weight: 700; }
        .step.active { border-color: #1d4ed8; color: #1d4ed8; }
        .step.active .num { background: #1d4ed8; color: #fff; }
        .step.done { color: #166534; }
        .step.done .num { background: #16a34a; color: #fff; }
        .two-col { display: grid; grid-template-columns: minmax(0, 2fr) minmax(240px, 1fr); gap: 16px; align-items: start; }
        .error { color: #b91c1c; font-size: 12px; margin-top: 4px; }
        .info { background: #eff6ff; border-radius: 6px; padding: 10px; color: #1e40af; font-size: 12px; margin-top: 12px; }
        .pagination { display: flex; gap: 4px; justify-content: flex-end; align-items: center; margin-top: 12px; }
        .pagination a, .pagination span { padding: 5px 10px; border: 1px solid #e2e8f0; border-radius: 5px; }
        .pagination .current { background: #1e3a8a; color: #fff; border-color: #1e3a8a; }
        .dropzone { border: 2px dashed #cbd5e1; border-radius: 8px; padding: 34px; text-align: center; color: #64748b; }
        .dropzone.dragover { background: #eff6ff; border-color: #1d4ed8; color: #1d4ed8; }
    </style>
    @stack('styles')
</head>
<body>
@php
    $roleLabel = match (auth()->user()->role) {
        'admin' => 'Supervisor',
        'petugas' => 'Operator',
        'direksi' => 'Direksi',
        default => ucfirst(auth()->user()->role),
    };
    $operatorMenu = [
        'Dashboard' => route('petugas.dashboard'),
        'Data Kapal' => null,
        'Buat Opname' => route('petugas.opnams.create'),
        'Opname Saya' => route('petugas.opnams.index'),
        'Status Pengajuan' => null,
        'Riwayat Aktivitas' => null,
        'Profil' => null,
    ];
    $supervisorMenu = [
        'Dashboard' => route('admin.dashboard'),
        'Data Pemilik Kapal' => null,
        'Data Kapal' => null,
        'Master Pekerjaan' => route('admin.work-categories.index'),
        'Jenis Pekerjaan' => route('admin.work-types.index'),
        'Master Tarif' => null,
        'Data Opname' => route('admin.opnams.index'),
        'Review Opname' => null,
        'Laporan' => null,
        'Riwayat Revisi' => null,
        'Riwayat Aktivitas' => null,
        'Manajemen Pengguna' => null,
        'Profil' => null,
    ];
    $menu = auth()->user()->role === 'admin' ? $supervisorMenu : $operatorMenu;
    $initials = collect(explode(' ', auth()->user()->name))->map(fn ($p) => mb_substr($p, 0, 1))->join('');
@endphp
<div class="app">
    <aside class="sidebar">
        <div class="brand" style="display: flex; align-items: center; gap: 10px;">
            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#1e3a8a" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 15h18l-2 4H5l-2-4Z"/>
                <path d="M12 3v9M7 12h10"/>
                <path d="M12 3c2 1.5 3 3.5 3 6h-6c0-2.5 1-4.5 3-6Z"/>
            </svg>
            <div>
                <strong>SIMSHIP</strong>
                <small>Shipyard Management</small>
            </div>
        </div>
        <nav class="nav">
            <p class="nav-label">Menu {{ $roleLabel }}</p>
            @foreach ($menu as $label => $url)
                @if ($url)
                    <a href="{{ $url }}" class="{{ request()->routeIs(match ($label) { 'Data Opname' => 'admin.opnams.*', 'Master Pekerjaan' => 'admin.work-categories.*', 'Jenis Pekerjaan' => 'admin.work-types.*', 'Buat Opname' => 'petugas.opnams.create', 'Opname Saya' => 'petugas.opnams.index', default => 'none' }) ? 'active' : '' }}">{{ $label }}</a>
                @else
                    <span class="item">{{ $label }}</span>
                @endif
            @endforeach
        </nav>
        <div class="profile">
            <div class="avatar">{{ $initials }}</div>
            <div><strong>{{ auth()->user()->name }}</strong><small>{{ $roleLabel }}</small></div>
        </div>
    </aside>
    <div class="main">
        <header class="topbar">
            <div class="crumbs">SIMSHIP &gt; <strong>@yield('crumb', 'Dashboard')</strong></div>
            <div class="topbar-user">
                <span aria-hidden="true">🔔</span>
                <div class="avatar">{{ $initials }}</div>
                <div><strong>{{ auth()->user()->name }}</strong><small>{{ $roleLabel }}</small></div>
            </div>
        </header>
        <main class="content">
            @if (session('status'))
                <div class="info" style="margin-bottom:16px;">{{ session('status') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>
