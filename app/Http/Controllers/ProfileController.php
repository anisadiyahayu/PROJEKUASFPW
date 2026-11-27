<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('profile.edit');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            return Redirect::route('profil.edit')
                ->withErrors(['current_password' => 'Password lama tidak sesuai.']);
        }

        $user->update(['password' => $data['password']]);

        return Redirect::route('profil.edit')
            ->with('status', 'Password berhasil diubah.');
    }
}
