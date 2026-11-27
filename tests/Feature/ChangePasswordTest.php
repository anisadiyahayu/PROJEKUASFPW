<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function penggunaProfil(string $role = 'petugas'): User
{
    static $id = 0;
    $id++;

    return User::create([
        'name' => ucfirst($role).' Profil '.$id,
        'username' => $role.'-profil-'.$id,
        'email' => $role.'-profil-'.$id.'@example.com',
        'password' => 'password',
        'role' => $role,
        'is_active' => true,
    ]);
}

test('ketiga role dapat membuka halaman profil yang sama', function () {
    foreach ([User::ROLE_PETUGAS, User::ROLE_ADMIN, User::ROLE_DIREKSI] as $role) {
        $user = penggunaProfil($role);

        $this->actingAs($user)
            ->get(route('profil.edit'))
            ->assertOk()
            ->assertSee('Password lama')
            ->assertSee('Password baru')
            ->assertSee('Simpan Password');
    }
});

test('tamu dialihkan ke halaman login', function () {
    $this->get(route('profil.edit'))
        ->assertRedirect(route('login'));
});

test('password lama yang salah ditolak dan password tidak berubah', function () {
    $user = penggunaProfil();
    $hashAwal = $user->password;

    $this->actingAs($user)->put(route('profil.password.update'), [
        'current_password' => 'salah',
        'password' => 'rahasia123',
    ])->assertSessionHasErrors('current_password');

    expect($user->fresh()->password)->toBe($hashAwal);
});

test('password lama wajib diisi', function () {
    $user = penggunaProfil();
    $hashAwal = $user->password;

    $this->actingAs($user)->put(route('profil.password.update'), [
        'current_password' => '',
        'password' => 'rahasia123',
    ])->assertSessionHasErrors('current_password');

    expect($user->fresh()->password)->toBe($hashAwal);
});

test('password baru minimal delapan karakter', function () {
    $user = penggunaProfil();
    $hashAwal = $user->password;

    $this->actingAs($user)->put(route('profil.password.update'), [
        'current_password' => 'password',
        'password' => 'pendek',
    ])->assertSessionHasErrors('password');

    expect($user->fresh()->password)->toBe($hashAwal);
});

test('password baru tersimpan ter-hash dan bukan teks polos', function () {
    $user = penggunaProfil();
    $plain = 'rahasia123';

    $this->actingAs($user)->put(route('profil.password.update'), [
        'current_password' => 'password',
        'password' => $plain,
    ])->assertRedirect(route('profil.edit'))
        ->assertSessionHas('status', 'Password berhasil diubah.');

    $user->refresh();

    expect($user->password)->not->toBe($plain)
        ->and(Hash::check($plain, $user->password))->toBeTrue();
});

test('sesi tetap aktif setelah ganti password dan login memakai password baru', function () {
    $user = penggunaProfil();

    $this->actingAs($user)->put(route('profil.password.update'), [
        'current_password' => 'password',
        'password' => 'rahasia123',
    ])->assertRedirect(route('profil.edit'));

    $this->assertAuthenticatedAs($user);
    $this->get(route('profil.edit'))->assertOk();

    $this->post('/logout');

    $this->post('/login', [
        'username' => $user->username,
        'password' => 'password',
    ])->assertSessionHasErrors('username');

    $this->post('/login', [
        'username' => $user->username,
        'password' => 'rahasia123',
    ])->assertRedirect(route($user->dashboardRoute()));
});
