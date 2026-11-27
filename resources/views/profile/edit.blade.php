@extends('layouts.simship')

@section('title', 'Profil')
@section('crumb', 'Profil')

@section('content')
    <h1>Profil</h1>
    <p class="subtitle">Ubah password akun Anda.</p>

    @if (session('status'))
        <div class="info" style="margin-bottom:16px;">{{ session('status') }}</div>
    @endif

    <div class="card">
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
