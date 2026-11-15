<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMSHIP - @yield('title')</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 0; background: #f4f6fa; color: #1f2937; }
        header { display: flex; justify-content: space-between; align-items: center; padding: 16px 24px; background: #1e3a8a; color: #fff; }
        main { padding: 24px; }
        button { background: #1e3a8a; color: #fff; border: 0; padding: 8px 14px; border-radius: 6px; cursor: pointer; }
        .card { background: #fff; padding: 24px; border-radius: 10px; max-width: 480px; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
    </style>
</head>
<body>
    <header>
        <strong>SIMSHIP</strong>
        <div>
            {{ auth()->user()->name }} ({{ ucfirst(auth()->user()->role) }})
            <form action="{{ route('logout') }}" method="POST" style="display:inline">
                @csrf
                <button type="submit">Logout</button>
            </form>
        </div>
    </header>
    <main>
        @yield('content')
    </main>
</body>
</html>