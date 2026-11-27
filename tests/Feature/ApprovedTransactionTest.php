<?php

use App\Models\Opnam;
use App\Models\Owner;
use App\Models\Ship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function buatUserDisetujui(string $role, string $username): User
{
    return User::create([
        'name' => ucfirst($role).' Disetujui',
        'username' => $username,
        'email' => $username.'@example.com',
        'password' => 'password',
        'role' => $role,
        'is_active' => true,
    ]);
}

function siapkanTransaksiDisetujui(): array
{
    $admin = buatUserDisetujui(User::ROLE_ADMIN, 'admin-disetujui');
    $owner = Owner::create(['name' => 'PT Disetujui Marine']);
    $ship = Ship::create([
        'owner_id' => $owner->id,
        'name' => 'KM Disetujui',
        'hull_number' => 'HULL-SETUJU-1',
    ]);

    $disetujui = Opnam::create([
        'opnam_number' => 'OPN-SETUJU-1',
        'ship_id' => $ship->id,
        'created_by' => $admin->id,
        'status' => Opnam::STATUS_DISETUJUI,
        'tanggal_opnam' => '2026-01-10',
        'total_biaya' => 500000.00,
    ]);

    $terbit = Opnam::create([
        'opnam_number' => 'OPN-SETUJU-2',
        'ship_id' => $ship->id,
        'created_by' => $admin->id,
        'status' => Opnam::STATUS_DOKUMEN_TERBIT,
        'tanggal_opnam' => '2026-01-11',
        'total_biaya' => 750000.00,
    ]);

    $draft = Opnam::create([
        'opnam_number' => 'OPN-SETUJU-DRAFT',
        'ship_id' => $ship->id,
        'created_by' => $admin->id,
        'status' => Opnam::STATUS_DRAFT,
        'tanggal_opnam' => '2026-01-12',
        'total_biaya' => 250000.00,
    ]);

    return [$admin, $ship, $disetujui, $terbit, $draft];
}

test('direksi hanya melihat transaksi yang sudah disetujui atau terbit', function () {
    [, , $disetujui, $terbit, $draft] = siapkanTransaksiDisetujui();
    $direksi = buatUserDisetujui(User::ROLE_DIREKSI, 'direksi-disetujui');

    $this->actingAs($direksi)
        ->get(route('direksi.approved.index'))
        ->assertOk()
        ->assertSee($disetujui->opnam_number)
        ->assertSee($terbit->opnam_number)
        ->assertDontSee($draft->opnam_number);
});

test('direksi dapat mencari transaksi disetujui berdasarkan kapal', function () {
    [, $ship] = siapkanTransaksiDisetujui();
    $direksi = buatUserDisetujui(User::ROLE_DIREKSI, 'direksi-disetujui');

    $this->actingAs($direksi)
        ->get(route('direksi.approved.index', ['q' => 'HULL-SETUJU-1']))
        ->assertOk()
        ->assertSee('OPN-SETUJU-1')
        ->assertSee('KM Disetujui');

    expect($ship->fresh())->not->toBeNull();
});

test('transaksi disetujui tetap tampil saat kapalnya sudah dihapus', function () {
    [, $ship, $disetujui] = siapkanTransaksiDisetujui();
    $direksi = buatUserDisetujui(User::ROLE_DIREKSI, 'direksi-disetujui');
    $ship->delete();

    $this->actingAs($direksi)
        ->get(route('direksi.approved.index'))
        ->assertOk()
        ->assertSee($disetujui->opnam_number)
        ->assertSee('KM Disetujui')
        ->assertSee('Dihapus');
});

test('admin tidak boleh mengakses halaman transaksi disetujui direksi', function () {
    [$admin] = siapkanTransaksiDisetujui();

    $this->actingAs($admin)
        ->get(route('direksi.approved.index'))
        ->assertForbidden();
});
