<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->orderBy('name')
            ->orderBy('username')
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'username' => strtolower(trim((string) $request->input('username'))),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in([
                User::ROLE_PETUGAS,
                User::ROLE_ADMIN,
                User::ROLE_DIREKSI,
            ])],
        ]);

        User::create($data);

        return Redirect::route('admin.users.index')
            ->with('status', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $request->merge([
            'username' => strtolower(trim((string) $request->input('username'))),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($user->getKey()),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->getKey()),
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', Rule::in([
                User::ROLE_PETUGAS,
                User::ROLE_ADMIN,
                User::ROLE_DIREKSI,
            ])],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return Redirect::route('admin.users.index')
            ->with('status', 'Pengguna berhasil diperbarui.');
    }

    public function deactivate(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return Redirect::route('admin.users.index')
                ->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $user->update(['is_active' => false]);

        return Redirect::route('admin.users.index')
            ->with('status', 'Pengguna berhasil dinonaktifkan.');
    }
}
