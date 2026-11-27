<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function penggunaProfilU5(string $role = 'petugas'): User
{
    static $id = 0;
    $id++;

    return User::create([
        'name' => ucfirst($role).' U5 '.$id,
        'username' => $role.'-u5-'.$id,
        'email' => $role.'-u5-'.$id.'@example.com',
        'password' => 'password',
        'role' => $role,
        'is_active' => true,
    ]);
}

test('halaman profil menampilkan name username dan email saat ini', function () {
    $user = penggunaProfilU5();

    $this->actingAs($user)
        ->get(route('profil.edit'))
        ->assertOk()
        ->assertSee($user->name)
        ->assertSee($user->username)
        ->assertSee($user->email);
});

test('update profil berhasil mengubah name username dan email', function () {
    $user = penggunaProfilU5();

    $this->actingAs($user)->put(route('profil.update'), [
        'name' => 'Nama Baru',
        'username' => 'namabaru',
        'email' => 'namabaru@example.com',
    ])->assertRedirect(route('profil.edit'))
        ->assertSessionHas('status', 'Profil berhasil diperbarui.');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Nama Baru',
        'username' => 'namabaru',
        'email' => 'namabaru@example.com',
    ]);
});

test('username dinormalisasi saat menyimpan profil', function () {
    $user = penggunaProfilU5();

    $this->actingAs($user)->put(route('profil.update'), [
        'name' => $user->name,
        'username' => '  DIREKSI1  ',
        'email' => $user->email,
    ])->assertRedirect(route('profil.edit'));

    $this->assertDatabaseHas('users', ['id' => $user->id, 'username' => 'direksi1']);
    $this->assertDatabaseMissing('users', ['username' => '  DIREKSI1  ']);
});

test('login dengan username baru berhasil', function () {
    $user = penggunaProfilU5();

    $this->actingAs($user)->put(route('profil.update'), [
        'name' => $user->name,
        'username' => 'petugasbaru',
        'email' => $user->email,
    ])->assertRedirect(route('profil.edit'));

    $this->post('/logout');

    $this->post('/login', [
        'username' => 'petugasbaru',
        'password' => 'password',
    ])->assertRedirect(route($user->dashboardRoute()));
});

test('username atau email milik akun lain ditolak sedangkan milik sendiri boleh', function () {
    $user = penggunaProfilU5();
    $lain = penggunaProfilU5('admin');

    $this->actingAs($user)->put(route('profil.update'), [
        'name' => $user->name,
        'username' => $user->username,
        'email' => $user->email,
    ])->assertSessionHasNoErrors();

    $this->actingAs($user)->put(route('profil.update'), [
        'name' => $user->name,
        'username' => $lain->username,
        'email' => $user->email,
    ])->assertSessionHasErrors('username');

    $this->actingAs($user)->put(route('profil.update'), [
        'name' => $user->name,
        'username' => $user->username,
        'email' => $lain->email,
    ])->assertSessionHasErrors('email');
});

test('role tidak berubah walau dikirim lewat form profil', function () {
    $user = penggunaProfilU5();

    $this->actingAs($user)->put(route('profil.update'), [
        'name' => $user->name,
        'username' => $user->username,
        'email' => $user->email,
        'role' => User::ROLE_ADMIN,
    ])->assertRedirect(route('profil.edit'));

    $this->assertDatabaseHas('users', ['id' => $user->id, 'role' => User::ROLE_PETUGAS]);
});

test('is_active tidak berubah walau dikirim lewat form profil', function () {
    $user = penggunaProfilU5();

    $this->actingAs($user)->put(route('profil.update'), [
        'name' => $user->name,
        'username' => $user->username,
        'email' => $user->email,
        'is_active' => false,
    ])->assertRedirect(route('profil.edit'));

    $this->assertDatabaseHas('users', ['id' => $user->id, 'is_active' => true]);
});

test('tamu dialihkan ke login', function () {
    $this->get(route('profil.edit'))
        ->assertRedirect(route('login'));

    $this->put(route('profil.update'), [
        'name' => 'Tamu',
        'username' => 'tamu',
        'email' => 'tamu@example.com',
    ])->assertRedirect(route('login'));
});
