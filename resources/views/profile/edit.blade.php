@extends('layouts.simship')

@section('title', 'Profil')
@section('crumb', 'Profil')

@section('content')
    <h1>Profil</h1>
    <p class="subtitle">Kelola data diri dan password akun Anda.</p>

    @if (session('status'))
        <div class="info" style="margin-bottom:16px;">{{ session('status') }}</div>
    @endif

    <div class="card">
        <h2 style="font-size:16px; margin:0 0 14px;">Data Diri</h2>
        <form method="POST" action="{{ route('profil.update') }}">
            @csrf
            @method('PUT')

            <div style="margin-bottom:16px;">
                <label for="name">Nama <span aria-hidden="true">*</span></label>
                <input id="name" type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required maxlength="255">
                @error('name')<div class="error">{{ $message }}</div>@enderror
            </div>

            <div style="margin-bottom:16px;">
                <label for="username">Username <span aria-hidden="true">*</span></label>
                <input id="username" type="text" name="username" value="{{ old('username', auth()->user()->username) }}" required maxlength="255">
                @error('username')<div class="error">{{ $message }}</div>@enderror
            </div>

            <div style="margin-bottom:16px;">
                <label for="email">Email <span aria-hidden="true">*</span></label>
                <input id="email" type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required maxlength="255">
                @error('email')<div class="error">{{ $message }}</div>@enderror
            </div>

            <div style="display:flex; gap:8px;">
                <button type="submit" class="btn btn-primary">Simpan Profil</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h2 style="font-size:16px; margin:0 0 14px;">Ubah Password</h2>
        <form method="POST" action="{{ route('profil.password.update') }}">
            @csrf
            @method('PUT')

            <div style="margin-bottom:16px;">
                <label for="current_password">Password lama <span aria-hidden="true">*</span></label>
                <input id="current_password" type="password" name="current_password" required autocomplete="current-password">
                @error('current_password')<div class="error">{{ $message }}</div>@enderror
            </div>

            <div style="margin-bottom:16px;">
                <label for="password">Password baru <span aria-hidden="true">*</span></label>
                <input id="password" type="password" name="password" required minlength="8" autocomplete="new-password">
                @error('password')<div class="error">{{ $message }}</div>@enderror
            </div>

            <div style="display:flex; gap:8px;">
                <button type="submit" class="btn btn-primary">Simpan Password</button>
            </div>
        </form>
    </div>
@endsection
