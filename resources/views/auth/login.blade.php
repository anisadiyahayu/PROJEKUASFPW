<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIMSHIP</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 0; background: #f4f6fa; color: #1f2937; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .card { background: #fff; padding: 32px; border-radius: 10px; width: 320px; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
        label { display: block; margin-bottom: 14px; font-size: 14px; }
        input[type=text], input[type=password] { width: 100%; padding: 8px; box-sizing: border-box; margin-top: 4px; border: 1px solid #cbd5e1; border-radius: 6px; }
        button { width: 100%; background: #1e3a8a; color: #fff; border: 0; padding: 10px; border-radius: 6px; cursor: pointer; }
        .error { color: #b91c1c; font-size: 14px; margin-bottom: 14px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Login SIMSHIP</h2>

        @error('username')
            <p class="error">{{ $message }}</p>
        @enderror

        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <label>
            Username / Email
                <input type="text" name="username" value="{{ old('username') }}"
                placeholder="admin@simship.com" autocomplete="username" required autofocus>
            </label>
            <label>
                Password
                <input type="password" name="password" autocomplete="current-password" required>
            </label>
            <label>
                <input type="checkbox" name="remember" value="1"> Ingat saya
            </label>
            <button type="submit">Masuk</button>
        </form>
    </div>
</body>
</html>