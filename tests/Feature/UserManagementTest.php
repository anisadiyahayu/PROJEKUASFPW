<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

function penggunaManajemen(string $role = 'admin'): User
{
    static $id = 0;
    $id++;

    return User::create([
        'name' => ucfirst($role).' Manajemen '.$id,
        'username' => $role.'-manajemen-'.$id,
        'email' => $role.'-manajemen-'.$id.'@example.com',
        'password' => 'password',
        'role' => $role,
        'is_active' => true,
    ]);
}

test('admin dapat melihat daftar pengguna', function () {
    $admin = penggunaManajemen();
    $target = penggunaManajemen('petugas');

    $this->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee($target->name);
});

test('admin dapat menambah pengguna dengan role yang ditentukan', function () {
    $admin = penggunaManajemen();

    $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Petugas Baru',
        'username' => 'petugas-baru',
        'email' => 'petugas-baru@example.com',
        'password' => 'rahasia123',
        'role' => User::ROLE_PETUGAS,
    ])->assertRedirect(route('admin.users.index'));

    $this->assertDatabaseHas('users', [
        'username' => 'petugas-baru',
        'email' => 'petugas-baru@example.com',
        'role' => User::ROLE_PETUGAS,
    ]);

    $pengguna = User::where('username', 'petugas-baru')->firstOrFail();
    expect(Hash::check('rahasia123', $pengguna->password))->toBeTrue();
});

test('validasi menolak username email duplikat dan role di luar daftar', function () {
    $admin = penggunaManajemen();
    $ada = penggunaManajemen('petugas');

    $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Duplikat',
        'username' => $ada->username,
        'email' => $ada->email,
        'password' => 'rahasia123',
        'role' => User::ROLE_PETUGAS,
    ])->assertSessionHasErrors(['username', 'email']);

    $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Role Salah',
        'username' => 'role-salah',
        'email' => 'role-salah@example.com',
        'password' => 'rahasia123',
        'role' => 'superadmin',
    ])->assertSessionHasErrors('role');
});

test('admin dapat mengubah data pengguna dan password kosong tidak mengubah password', function () {
    $admin = penggunaManajemen();
    $target = penggunaManajemen('petugas');
    $hashAwal = $target->password;

    $this->actingAs($admin)->put(route('admin.users.update', $target), [
        'name' => 'Petugas Diubah',
        'username' => $target->username,
        'email' => $target->email,
        'password' => '',
        'role' => User::ROLE_DIREKSI,
    ])->assertRedirect(route('admin.users.index'));

    $target->refresh();
    expect($target->name)->toBe('Petugas Diubah')
        ->and($target->role)->toBe(User::ROLE_DIREKSI)
        ->and($target->password)->toBe($hashAwal);

    $this->actingAs($admin)->put(route('admin.users.update', $target), [
        'name' => $target->name,
        'username' => $target->username,
        'email' => $target->email,
        'password' => 'rahasia123',
        'role' => $target->role,
    ])->assertRedirect(route('admin.users.index'));

    $target->refresh();
    expect(Hash::check('rahasia123', $target->password))->toBeTrue();
});

test('nonaktifkan pengguna mengubah is_active dan mencegah login', function () {
    $admin = penggunaManajemen();
    $target = penggunaManajemen('petugas');

    $this->actingAs($admin)
        ->patch(route('admin.users.deactivate', $target))
        ->assertRedirect(route('admin.users.index'));

    $this->assertDatabaseHas('users', ['id' => $target->id, 'is_active' => false]);

    $this->post('/logout');

    $this->post('/login', [
        'username' => $target->username,
        'password' => 'password',
    ])->assertSessionHasErrors('username');

    $this->assertGuest();
});

test('petugas tidak dapat mengakses manajemen pengguna', function () {
    $petugas = penggunaManajemen('petugas');

    $this->actingAs($petugas)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

test('tidak ada rute hapus pengguna', function () {
    expect(Route::has('admin.users.destroy'))->toBeFalse();
});

test('admin tidak dapat menonaktifkan akunnya sendiri', function () {
    $admin = penggunaManajemen();

    $this->actingAs($admin)
        ->patch(route('admin.users.deactivate', $admin))
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('error', 'Anda tidak dapat menonaktifkan akun sendiri.');

    $this->assertDatabaseHas('users', ['id' => $admin->id, 'is_active' => true]);
    expect($admin->fresh()->is_active)->toBeTrue();
});
