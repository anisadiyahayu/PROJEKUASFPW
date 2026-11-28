<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMSHIP - @yield('title')</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: system-ui, -apple-system, sans-serif;
            background: #f4f7fb;
            color: #1e293b;
            font-size: 14px;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .app {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 232px;
            flex: 0 0 232px;
            background: #fff;
            border-right: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
        }

        .brand {
            padding: 20px 18px;
            border-bottom: 1px solid #e2e8f0;
        }

        .brand strong {
            display: block;
            color: #1e3a8a;
            font-size: 17px;
            letter-spacing: .04em;
        }

        .brand small {
            color: #94a3b8;
            font-size: 11px;
        }

        .nav {
            padding: 18px 12px;
            flex: 1;
        }

        .nav-label {
            color: #94a3b8;
            font-size: 11px;
            letter-spacing: .08em;
            margin: 0 8px 10px;
            text-transform: uppercase;
        }

        .nav a,
        .nav span.item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 10px;
            border-radius: 6px;
            margin-bottom: 3px;
            color: #475569;
        }

        .nav a:hover {
            background: #f1f5f9;
        }

        .nav .active {
            background: #eff6ff;
            color: #1d4ed8;
            font-weight: 600;
            border-left: 3px solid #1d4ed8;
            padding-left: 7px;
        }

        .nav span.item {
            color: #94a3b8;
            cursor: default;
        }

        .nav .divider {
            height: 1px;
            background: #e2e8f0;
            margin: 16px 8px;
        }

        .profile {
            border-top: 1px solid #e2e8f0;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #1e3a8a;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
        }

        .profile small {
            display: block;
            color: #94a3b8;
        }

        .main {
            flex: 1;
            min-width: 0;
        }

        .topbar {
            height: 66px;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
        }

        .crumbs {
            color: #64748b;
            font-size: 13px;
        }

        .crumbs strong {
            color: #0f172a;
        }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .topbar-user small {
            display: block;
            color: #94a3b8;
            font-size: 11px;
        }

        .content {
            padding: 24px;
        }

        h1 {
            font-size: 26px;
            margin: 0 0 6px;
        }

        .subtitle {
            color: #64748b;
            margin: 0 0 20px;
        }

        .card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 16px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border: 0;
            border-radius: 6px;
            padding: 9px 16px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
        }

        .btn-primary {
            background: #1e3a8a;
            color: #fff;
        }

        .btn-light {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #e2e8f0;
        }

        .btn:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

        input[type=text],
        input[type=date],
        input[type=number],
        input[type=file],
        select,
        textarea {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font: inherit;
            background: #fff;
        }

        label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            color: #64748b;
            font-size: 11px;
            letter-spacing: .04em;
            padding: 10px 8px;
            border-bottom: 1px solid #e2e8f0;
        }

        td {
            padding: 12px 8px;
            border-bottom: 1px solid #f1f5f9;
        }

        .badge {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 999px;
            font-size: 11px;
            background: #f1f5f9;
            color: #475569;
        }

        .badge-draft {
            background: #f1f5f9;
            color: #475569;
        }

        .badge-menunggu_review {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-perlu_revisi {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-menunggu_approval_direksi {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-ditolak {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-disetujui {
            background: #dcfce7;
            color: #166534;
        }

        .badge-dokumen_terbit {
            background: #ede9fe;
            color: #5b21b6;
        }

        .badge-menunggu_approval {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-terbit {
            background: #dcfce7;
            color: #166534;
        }

        .summary {
            background: #f8fafc;
            border-radius: 8px;
            padding: 14px;
        }

        .summary .row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            color: #64748b;
        }

        .summary .total {
            border-top: 1px solid #e2e8f0;
            margin-top: 8px;
            padding-top: 10px;
            font-weight: 700;
            color: #1e3a8a;
            font-size: 18px;
        }

        .steps {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }

        .step {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            color: #94a3b8;
            font-size: 12px;
        }

        .step .num {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .step.active {
            border-color: #1d4ed8;
            color: #1d4ed8;
        }

        .step.active .num {
            background: #1d4ed8;
            color: #fff;
        }

        .step.done {
            color: #166534;
        }

        .step.done .num {
            background: #16a34a;
            color: #fff;
        }

        .two-col {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(240px, 1fr);
            gap: 16px;
            align-items: start;
        }

        .error {
            color: #b91c1c;
            font-size: 12px;
            margin-top: 4px;
        }

        .info {
            background: #eff6ff;
            border-radius: 6px;
            padding: 10px;
            color: #1e40af;
            font-size: 12px;
            margin-top: 12px;
        }

        .pagination {
            display: flex;
            gap: 4px;
            justify-content: flex-end;
            align-items: center;
            margin-top: 12px;
        }

        .pagination a,
        .pagination span {
            padding: 5px 10px;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
        }

        .pagination .current {
            background: #1e3a8a;
            color: #fff;
            border-color: #1e3a8a;
        }

        .dropzone {
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
            padding: 34px;
            text-align: center;
            color: #64748b;
        }

        .dropzone.dragover {
            background: #eff6ff;
            border-color: #1d4ed8;
            color: #1d4ed8;
        }

        .btn {
            transition: background-color .15s ease, border-color .15s ease, color .15s ease;
        }

        .btn-primary:hover {
            background: #1e40af;
        }

        .btn-light:hover {
            background: #e2e8f0;
        }

        a:hover {
            text-decoration: underline;
        }

        a,
        button,
        select,
        label[for] {
            cursor: pointer;
        }

        :focus-visible {
            outline: 2px solid #1d4ed8;
            outline-offset: 2px;
        }

        .table-wrap {
            overflow-x: auto;
        }

        .actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .stack {
            display: grid;
            gap: 14px;
        }

        .muted {
            color: #64748b;
        }

        .nowrap {
            white-space: nowrap;
        }

        .right {
            text-align: right;
        }

        .empty {
            text-align: center;
            color: #64748b;
            padding: 20px;
        }

        @media (max-width: 760px) {
            .app {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                flex: none;
                border-right: 0;
                border-bottom: 1px solid #e2e8f0;
            }

            .nav {
                display: flex;
                flex-wrap: wrap;
                gap: 4px;
            }

            .nav a,
            .nav span.item {
                margin-bottom: 0;
            }

            .nav-label,
            .nav .divider {
                display: none;
            }

            .topbar {
                padding: 0 16px;
            }

            .content {
                padding: 16px;
            }

            .grid,
            .two-col {
                grid-template-columns: 1fr;
            }

            .form-row {
                flex-direction: column;
                align-items: stretch;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .btn {
                transition: none;
            }
        }

        .btn-danger {
            background: #b91c1c;
            color: #fff;
        }

        .btn-sm {
            padding: 5px 9px;
            font-size: 12px;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 16px;
        }

        .muted {
            color: #64748b;
        }

        .table-wrap {
            overflow-x: auto;
        }

        .empty {
            text-align: center;
            color: #94a3b8;
            padding: 28px 12px;
        }

        .form-actions {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

        .field-error {
            color: #b91c1c;
            font-size: 12px;
            margin-top: 4px;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 16px;
        }

        .stat {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 16px;
        }

        .stat small {
            color: #64748b;
            font-size: 12px;
        }

        .stat strong {
            display: block;
            font-size: 22px;
            color: #1e3a8a;
            margin-top: 4px;
        }

        .actions {
            display: flex;
            gap: 6px;
            align-items: center;
            flex-wrap: wrap;
        }

        .actions form {
            display: inline;
        }

        .filters {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .filters>div {
            min-width: 150px;
        }

        details.changes summary {
            cursor: pointer;
            color: #1d4ed8;
            font-size: 12px;
        }

        details.changes pre {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px;
            font-size: 11px;
            overflow-x: auto;
        }
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
    $menus = [
    'petugas' => [
    ['label' => 'Dashboard', 'url' => route('petugas.dashboard'), 'active' => 'petugas.dashboard'],
    ['label' => 'Data Kapal', 'url' => route('petugas.ships.index'), 'active' => 'petugas.ships.index'],
    ['label' => 'Buat Opname', 'url' => route('petugas.opnams.create'), 'active' => 'petugas.opnams.create'],
    ['label' => 'Opname Saya', 'url' => route('petugas.opnams.index'), 'active' => 'petugas.opnams.index'],
    ['label' => 'Riwayat Aktivitas', 'url' => route('activity-logs.index'), 'active' => 'activity-logs.*'],
    ['label' => 'Profil', 'url' => route('profil.edit'), 'active' => 'profil.*'],
    ],
    'admin' => [
    ['label' => 'Dashboard', 'url' => route('admin.dashboard'), 'active' => 'admin.dashboard'],
    ['label' => 'Data Pemilik Kapal', 'url' => route('admin.owners.index'), 'active' => 'admin.owners.*'],
    ['label' => 'Data Kapal', 'url' => route('admin.ships.index'), 'active' => 'admin.ships.*'],
    ['label' => 'Kategori Pekerjaan', 'url' => route('admin.work-categories.index'), 'active' => 'admin.work-categories.*'],
    ['label' => 'Jenis Pekerjaan', 'url' => route('admin.work-types.index'), 'active' => 'admin.work-types.*'],
    ['label' => 'Material/Spesifikasi', 'url' => route('admin.material-specs.index'), 'active' => 'admin.material-specs.*'],
    ['label' => 'Satuan', 'url' => route('admin.units.index'), 'active' => 'admin.units.*'],
    ['label' => 'Data Opname', 'url' => route('admin.opnams.index'), 'active' => 'admin.opnams.*'],
    ['label' => 'Review Opname', 'url' => route('admin.review-opnams.index'), 'active' => 'admin.review-opnams.*'],
    ['label' => 'Laporan Rekapitulasi', 'url' => route('reports.recapitulation.index'), 'active' => 'reports.recapitulation.*'],
    ['label' => 'Riwayat Perbaikan', 'url' => route('repair-history.index'), 'active' => 'repair-history.*'],
    ['label' => 'Riwayat Aktivitas', 'url' => route('activity-logs.index'), 'active' => 'activity-logs.*'],
    ['label' => 'Manajemen Pengguna', 'url' => route('admin.users.index'), 'active' => 'admin.users.*'],
    ['label' => 'Profil', 'url' => route('profil.edit'), 'active' => 'profil.*'],
    ],
    'direksi' => [
    ['label' => 'Dashboard', 'url' => route('direksi.dashboard'), 'active' => 'direksi.dashboard'],
    ['label' => 'Tagihan', 'url' => route('direksi.tagihans.index'), 'active' => 'direksi.tagihans.*'],
    ['label' => 'Dokumen', 'url' => route('direksi.dokumen.index'), 'active' => 'direksi.dokumen.*'],
    ['label' => 'Transaksi Disetujui', 'url' => route('direksi.approved.index'), 'active' => 'direksi.approved.*'],
    ['label' => 'Riwayat Perbaikan', 'url' => route('repair-history.index'), 'active' => 'repair-history.*'],
    ['label' => 'Laporan Rekapitulasi', 'url' => route('reports.recapitulation.index'), 'active' => 'reports.recapitulation.*'],
    ['label' => 'Riwayat Aktivitas', 'url' => route('activity-logs.index'), 'active' => 'activity-logs.*'],
    ['label' => 'Profil', 'url' => route('profil.edit'), 'active' => 'profil.*'],
    ],
    ];
    $menu = $menus[auth()->user()->role] ?? $menus['petugas'];
    $initials = collect(explode(' ', auth()->user()->name))->map(fn ($p) => mb_substr($p, 0, 1))->join('');
    @endphp
    <div class="app">
        <aside class="sidebar">
            <div class="brand" style="display: flex; align-items: center; gap: 10px;">
                <img src="{{ asset('images/logo.png') }}" alt="Logo PT" style="height:40px;">
                <div>
                    <strong>SIMSHIP</strong>
                    <small>Shipyard Management</small>
                </div>
            </div>
            <nav class="nav">
                <p class="nav-label">Menu {{ $roleLabel }}</p>
                @foreach ($menu as $item)
                @if ($item['url'])
                <a href="{{ $item['url'] }}" class="{{ request()->routeIs($item['active'] ?? 'none') ? 'active' : '' }}">{{ $item['label'] }}</a>
                @else
                <span class="item">{{ $item['label'] }}</span>
                @endif
                @endforeach
            </nav>
            <div class="profile">
                <div class="avatar">{{ $initials }}</div>
                <div><strong>{{ auth()->user()->name }}</strong><small>{{ $roleLabel }}</small></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-light btn-sm">Logout</button>
                </form>
            </div>
        </aside>
        <div class="main">
            <header class="topbar">
                <div class="crumbs">SIMSHIP &gt; <strong>@yield('crumb', 'Dashboard')</strong></div>
                <div class="topbar-user">
                    <div class="avatar">{{ $initials }}</div>
                    <div><strong>{{ auth()->user()->name }}</strong><small>{{ $roleLabel }}</small></div>
                </div>
            </header>
            <main class="content">
                @if (session('status'))
                <div class="info" style="margin-bottom:16px;">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                <div class="alert-error">
                    <strong>Periksa kembali data berikut:</strong>
                    <ul style="margin: 6px 0 0; padding-left: 18px;">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>

</html>