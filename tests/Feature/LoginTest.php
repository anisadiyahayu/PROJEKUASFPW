<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private const ERROR_MESSAGE = 'Username/email atau password salah, atau akun tidak aktif.';

    public function test_login_dengan_username_yang_benar(): void
    {
        $user = User::factory()->create([
            'username' => 'petugas1',
            'email' => 'petugas1@example.com',
            'role' => User::ROLE_PETUGAS,
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'username' => $user->username,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('petugas.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_dengan_email_yang_benar(): void
    {
        $user = User::factory()->create([
            'username' => 'admin1',
            'email' => 'admin1@example.com',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'username' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_username_huruf_besar_dan_spasi_di_awal_atau_akhir(): void
    {
        $user = User::factory()->create([
            'username' => 'direksi1',
            'role' => User::ROLE_DIREKSI,
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'username' => '  DIREKSI1  ',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('direksi.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_password_salah_gagal_dan_tidak_ada_session_login(): void
    {
        $user = User::factory()->create([
            'username' => 'petugas2',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'username' => $user->username,
            'password' => 'salah',
        ]);

        $response->assertSessionHasErrors(['username' => self::ERROR_MESSAGE]);
        $this->assertGuest();
    }

    public function test_akun_nonaktif_tidak_dapat_login(): void
    {
        $user = User::factory()->create([
            'username' => 'nonaktif1',
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'username' => $user->username,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['username' => self::ERROR_MESSAGE]);
        $this->assertGuest();
    }

    public function test_username_tidak_ada_gagal_dengan_pesan_yang_sama(): void
    {
        User::factory()->create([
            'username' => 'ada1',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'username' => 'tidakada',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['username' => self::ERROR_MESSAGE]);
        $this->assertGuest();
    }

    public function test_login_berhasil_mengisi_last_login_at(): void
    {
        $user = User::factory()->create([
            'username' => 'login1',
            'is_active' => true,
        ]);

        $this->assertNull($user->last_login_at);

        $response = $this->post('/login', [
            'username' => $user->username,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('petugas.dashboard'));
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_logout_menghapus_session_dan_kembali_ke_login(): void
    {
        $user = User::factory()->create([
            'username' => 'logout1',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_post_logout_sebagai_guest_diarahkan_ke_login(): void
    {
        $response = $this->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
