<?php

use App\Models\Owner;
use App\Models\Ship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function buatAdminPemilik(): User
{
    return User::create([
        'name' => 'Admin Pemilik',
        'username' => 'admin-pemilik',
        'email' => 'admin-pemilik@example.com',
        'password' => 'password',
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);
}

function buatPetugasPemilik(): User
{
    return User::create([
        'name' => 'Petugas Pemilik',
        'username' => 'petugas-pemilik',
        'email' => 'petugas-pemilik@example.com',
        'password' => 'password',
        'role' => User::ROLE_PETUGAS,
        'is_active' => true,
    ]);
}

test('admin dapat melihat daftar pemilik kapal', function () {
    $admin = buatAdminPemilik();
    Owner::create(['name' => 'PT Samudra Jaya', 'address' => 'Surabaya']);

    $this->actingAs($admin)
        ->get(route('admin.owners.index'))
        ->assertOk()
        ->assertSee('PT Samudra Jaya')
        ->assertSee('Surabaya');
});

test('admin dapat menambah pemilik kapal', function () {
    $admin = buatAdminPemilik();

    $this->actingAs($admin)
        ->post(route('admin.owners.store'), [
            'name' => 'CV Bahari Sentosa',
            'address' => 'Makassar',
        ])
        ->assertRedirect(route('admin.owners.index'));

    $this->assertDatabaseHas('owners', [
        'name' => 'CV Bahari Sentosa',
        'address' => 'Makassar',
        'deleted_at' => null,
    ]);
});

test('nama pemilik wajib diisi', function () {
    $admin = buatAdminPemilik();

    $this->actingAs($admin)
        ->post(route('admin.owners.store'), ['name' => '', 'address' => 'Jakarta'])
        ->assertSessionHasErrors('name');
});

test('admin dapat memperbarui pemilik kapal', function () {
    $admin = buatAdminPemilik();
    $owner = Owner::create(['name' => 'Nama Lama', 'address' => 'Lama']);

    $this->actingAs($admin)
        ->put(route('admin.owners.update', $owner), [
            'name' => 'Nama Baru',
            'address' => 'Alamat Baru',
        ])
        ->assertRedirect(route('admin.owners.index'));

    expect($owner->fresh()->name)->toBe('Nama Baru')
        ->and($owner->fresh()->address)->toBe('Alamat Baru');
});

test('admin dapat menghapus pemilik yang tidak memiliki kapal', function () {
    $admin = buatAdminPemilik();
    $owner = Owner::create(['name' => 'Pemilik Tanpa Kapal']);

    $this->actingAs($admin)
        ->delete(route('admin.owners.destroy', $owner))
        ->assertRedirect(route('admin.owners.index'));

    $this->assertSoftDeleted('owners', ['id' => $owner->id]);
});

test('pemilik yang masih memiliki kapal tidak dapat dihapus', function () {
    $admin = buatAdminPemilik();
    $owner = Owner::create(['name' => 'Pemilik Dengan Kapal']);
    Ship::create([
        'owner_id' => $owner->id,
        'name' => 'Kapal Uji',
        'hull_number' => 'HULL-OWNER-1',
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.owners.destroy', $owner))
        ->assertRedirect(route('admin.owners.index'))
        ->assertSessionHasErrors('owner');

    $this->assertDatabaseHas('owners', ['id' => $owner->id, 'deleted_at' => null]);
});

test('pencarian pemilik mencocokkan nama dan alamat', function () {
    $admin = buatAdminPemilik();
    Owner::create(['name' => 'PT Alfa Marine', 'address' => 'Batam']);
    Owner::create(['name' => 'PT Beta Marine', 'address' => 'Bitung']);

    $this->actingAs($admin)
        ->get(route('admin.owners.index', ['q' => 'Alfa']))
        ->assertOk()
        ->assertSee('PT Alfa Marine')
        ->assertDontSee('PT Beta Marine');

    $this->actingAs($admin)
        ->get(route('admin.owners.index', ['q' => 'Bitung']))
        ->assertOk()
        ->assertSee('PT Beta Marine')
        ->assertDontSee('PT Alfa Marine');
});

test('form tambah dan edit pemilik dapat ditampilkan', function () {
    $admin = buatAdminPemilik();
    $owner = Owner::create(['name' => 'Pemilik Form']);

    $this->actingAs($admin)
        ->get(route('admin.owners.create'))
        ->assertOk()
        ->assertSee('Tambah Pemilik Kapal')
        ->assertSee('Nama Pemilik');

    $this->actingAs($admin)
        ->get(route('admin.owners.edit', $owner))
        ->assertOk()
        ->assertSee('Edit Pemilik Kapal')
        ->assertSee('Pemilik Form');
});

test('petugas tidak boleh mengakses pengelolaan pemilik', function () {
    $petugas = buatPetugasPemilik();

    $this->actingAs($petugas)
        ->get(route('admin.owners.index'))
        ->assertForbidden();
});
