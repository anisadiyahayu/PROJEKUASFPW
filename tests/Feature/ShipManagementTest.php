<?php

use App\Models\Owner;
use App\Models\Ship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function buatAdminKapal(): User
{
    return User::create([
        'name' => 'Admin Kapal',
        'username' => 'admin-kapal',
        'email' => 'admin-kapal@example.com',
        'password' => 'password',
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);
}

function buatPetugasKapal(): User
{
    return User::create([
        'name' => 'Petugas Kapal',
        'username' => 'petugas-kapal',
        'email' => 'petugas-kapal@example.com',
        'password' => 'password',
        'role' => User::ROLE_PETUGAS,
        'is_active' => true,
    ]);
}

test('admin dapat melihat daftar kapal beserta pemiliknya', function () {
    $admin = buatAdminKapal();
    $owner = Owner::create(['name' => 'PT Laut Biru']);
    Ship::create([
        'owner_id' => $owner->id,
        'name' => 'KM Bahari',
        'hull_number' => 'HULL-KAPAL-1',
        'jenis_kapal' => 'Tugboat',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.ships.index'))
        ->assertOk()
        ->assertSee('KM Bahari')
        ->assertSee('HULL-KAPAL-1')
        ->assertSee('Tugboat')
        ->assertSee('PT Laut Biru');
});

test('admin dapat menambah kapal dan menautkannya ke pemilik', function () {
    $admin = buatAdminKapal();
    $owner = Owner::create(['name' => 'PT Nusantara']);

    $this->actingAs($admin)
        ->post(route('admin.ships.store'), [
            'owner_id' => $owner->id,
            'name' => 'KM Nusantara',
            'hull_number' => 'HULL-BARU-1',
            'jenis_kapal' => 'Barge',
        ])
        ->assertRedirect(route('admin.ships.index'));

    $this->assertDatabaseHas('ships', [
        'owner_id' => $owner->id,
        'name' => 'KM Nusantara',
        'hull_number' => 'HULL-BARU-1',
        'jenis_kapal' => 'Barge',
    ]);
});

test('pemilik wajib dipilih saat menambah kapal', function () {
    $admin = buatAdminKapal();

    $this->actingAs($admin)
        ->post(route('admin.ships.store'), [
            'owner_id' => '',
            'name' => 'KM Tanpa Pemilik',
            'hull_number' => 'HULL-TANPA-1',
        ])
        ->assertSessionHasErrors('owner_id');
});

test('pemilik yang sudah dihapus tidak dapat dipilih', function () {
    $admin = buatAdminKapal();
    $owner = Owner::create(['name' => 'Pemilik Dihapus']);
    $owner->delete();

    $this->actingAs($admin)
        ->post(route('admin.ships.store'), [
            'owner_id' => $owner->id,
            'name' => 'KM Yatim',
            'hull_number' => 'HULL-YATIM-1',
        ])
        ->assertSessionHasErrors('owner_id');
});

test('nomor lambung harus unik', function () {
    $admin = buatAdminKapal();
    $owner = Owner::create(['name' => 'PT Unik']);
    Ship::create([
        'owner_id' => $owner->id,
        'name' => 'KM Pertama',
        'hull_number' => 'HULL-DOUBLE',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.ships.store'), [
            'owner_id' => $owner->id,
            'name' => 'KM Kedua',
            'hull_number' => 'HULL-DOUBLE',
        ])
        ->assertSessionHasErrors('hull_number');
});

test('admin dapat memperbarui data kapal', function () {
    $admin = buatAdminKapal();
    $ownerLama = Owner::create(['name' => 'Pemilik Lama']);
    $ownerBaru = Owner::create(['name' => 'Pemilik Baru']);
    $ship = Ship::create([
        'owner_id' => $ownerLama->id,
        'name' => 'KM Lama',
        'hull_number' => 'HULL-UBAH-1',
    ]);

    $this->actingAs($admin)
        ->put(route('admin.ships.update', $ship), [
            'owner_id' => $ownerBaru->id,
            'name' => 'KM Baru',
            'hull_number' => 'HULL-UBAH-1',
            'jenis_kapal' => 'Ferry',
        ])
        ->assertRedirect(route('admin.ships.index'));

    $ship->refresh();
    expect($ship->name)->toBe('KM Baru')
        ->and($ship->owner_id)->toBe($ownerBaru->id)
        ->and($ship->jenis_kapal)->toBe('Ferry');
});

test('admin dapat menghapus kapal dengan soft delete', function () {
    $admin = buatAdminKapal();
    $owner = Owner::create(['name' => 'PT Hapus']);
    $ship = Ship::create([
        'owner_id' => $owner->id,
        'name' => 'KM Hapus',
        'hull_number' => 'HULL-HAPUS-1',
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.ships.destroy', $ship))
        ->assertRedirect(route('admin.ships.index'));

    $this->assertSoftDeleted('ships', ['id' => $ship->id]);
});

test('daftar kapal dapat difilter berdasarkan pemilik', function () {
    $admin = buatAdminKapal();
    $ownerSatu = Owner::create(['name' => 'Pemilik Satu']);
    $ownerDua = Owner::create(['name' => 'Pemilik Dua']);
    Ship::create(['owner_id' => $ownerSatu->id, 'name' => 'KM Satu', 'hull_number' => 'HULL-FILTER-1']);
    Ship::create(['owner_id' => $ownerDua->id, 'name' => 'KM Dua', 'hull_number' => 'HULL-FILTER-2']);

    $this->actingAs($admin)
        ->get(route('admin.ships.index', ['owner_id' => $ownerSatu->id]))
        ->assertOk()
        ->assertSee('KM Satu')
        ->assertDontSee('KM Dua');
});

test('form tambah dan edit kapal dapat ditampilkan', function () {
    $admin = buatAdminKapal();
    $owner = Owner::create(['name' => 'Pemilik Form Kapal']);
    $ship = Ship::create([
        'owner_id' => $owner->id,
        'name' => 'KM Form',
        'hull_number' => 'HULL-FORM-1',
        'jenis_kapal' => 'Tugboat',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.ships.create', ['owner_id' => $owner->id]))
        ->assertOk()
        ->assertSee('Tambah Kapal')
        ->assertSee('Pemilik Form Kapal');

    $this->actingAs($admin)
        ->get(route('admin.ships.edit', $ship))
        ->assertOk()
        ->assertSee('Edit Kapal')
        ->assertSee('KM Form')
        ->assertSee('Tugboat');
});

test('petugas tidak boleh mengakses pengelolaan kapal', function () {
    $petugas = buatPetugasKapal();

    $this->actingAs($petugas)
        ->get(route('admin.ships.index'))
        ->assertForbidden();
});

test('jenis kapal memakai daftar pilihan tetap', function () {
    $admin = buatAdminKapal();
    $owner = Owner::create(['name' => 'PT Daftar Jenis']);

    $this->actingAs($admin)
        ->get(route('admin.ships.create'))
        ->assertOk()
        ->assertSee('Tugboat')
        ->assertSee('Barge')
        ->assertSee('Ferry')
        ->assertSee('Kapal Kargo');

    $this->actingAs($admin)
        ->post(route('admin.ships.store'), [
            'owner_id' => $owner->id,
            'name' => 'KM Daftar',
            'hull_number' => 'HULL-DAFTAR-1',
            'jenis_kapal' => 'Ferry',
        ])
        ->assertRedirect(route('admin.ships.index'));

    $this->assertDatabaseHas('ships', [
        'hull_number' => 'HULL-DAFTAR-1',
        'jenis_kapal' => 'Ferry',
    ]);
});

test('jenis kapal di luar daftar ditolak', function () {
    $admin = buatAdminKapal();
    $owner = Owner::create(['name' => 'PT Jenis Salah']);

    $this->actingAs($admin)
        ->post(route('admin.ships.store'), [
            'owner_id' => $owner->id,
            'name' => 'KM Salah',
            'hull_number' => 'HULL-SALAH-1',
            'jenis_kapal' => 'Kapal Angkut',
        ])
        ->assertSessionHasErrors('jenis_kapal');
});

test('jenis kapal lama di luar daftar tetap boleh dipertahankan saat kapal diubah', function () {
    $admin = buatAdminKapal();
    $owner = Owner::create(['name' => 'PT Jenis Lama']);
    $ship = Ship::create([
        'owner_id' => $owner->id,
        'name' => 'KM Lama',
        'hull_number' => 'HULL-LAMA-1',
        'jenis_kapal' => 'Kapal Lama',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.ships.edit', $ship))
        ->assertOk()
        ->assertSee('Kapal Lama');

    $this->actingAs($admin)
        ->put(route('admin.ships.update', $ship), [
            'owner_id' => $owner->id,
            'name' => 'KM Lama Revisi',
            'hull_number' => 'HULL-LAMA-1',
            'jenis_kapal' => 'Kapal Lama',
        ])
        ->assertRedirect(route('admin.ships.index'));

    $this->assertDatabaseHas('ships', [
        'id' => $ship->id,
        'jenis_kapal' => 'Kapal Lama',
    ]);
});
