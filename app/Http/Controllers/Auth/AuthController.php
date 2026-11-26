<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $identifier = strtolower(trim($data['username']));

        $user = User::query()
            ->where('username', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        if (! $user || ! Auth::attempt([
            'username' => $user->username,
            'password' => $data['password'],
            'is_active' => true,
        ], $request->boolean('remember'))) {
            return back()
                ->withErrors(['username' => 'Username/email atau password salah, atau akun tidak aktif.'])
                ->onlyInput('username');
        }

        $request->user()->update(['last_login_at' => now()]);
        $request->session()->regenerate();

        return redirect()->route($request->user()->dashboardRoute());
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
