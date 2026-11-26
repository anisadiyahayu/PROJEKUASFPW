<?php

use App\Models\Opnam;
use App\Models\Owner;
use App\Models\Ship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function adminUntukDaftarOpnam(): User
{
    return User::create([
        'name' => 'Admin',
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => 'password',
        'role' => 'admin',
        'is_active' => true,
    ]);
}

function kapalUntukDaftarOpnam(string $nama, string $hull): Ship
{
    $owner = Owner::create(['name' => 'Owner '.$hull]);

    return Ship::create([
        'owner_id' => $owner->id,
        'name' => $nama,
        'hull_number' => $hull,
    ]);
}

function opnamUntukDaftar(User $creator, Ship $ship, string $number, string $status, string $tanggal): Opnam
{
    return Opnam::create([
        'opnam_number' => $number,
        'ship_id' => $ship->id,
        'created_by' => $creator->id,
        'status' => $status,
        'tanggal_opnam' => $tanggal,
        'total_biaya' => 100.00,
    ]);
}

test('admin dapat melihat daftar opnam', function () {
    $admin = adminUntukDaftarOpnam();
    $ship = kapalUntukDaftarOpnam('Kapal Satu', 'HULL-001');
    opnamUntukDaftar($admin, $ship, 'OPN-001', Opnam::STATUS_DRAFT, '2026-01-05');

    $this->actingAs($admin)
        ->get(route('admin.opnams.index'))
        ->assertOk()
        ->assertSee('OPN-001')
        ->assertSee('Kapal Satu');
});

test('pencarian mencocokkan nomor opnam dan kapal', function () {
    $admin = adminUntukDaftarOpnam();
    $shipSatu = kapalUntukDaftarOpnam('Kapal Alfa', 'HULL-101');
    $shipDua = kapalUntukDaftarOpnam('Kapal Beta', 'HULL-202');
    opnamUntukDaftar($admin, $shipSatu, 'OPN-100', Opnam::STATUS_DRAFT, '2026-01-05');
    opnamUntukDaftar($admin, $shipDua, 'OPN-200', Opnam::STATUS_DRAFT, '2026-01-06');

    $this->actingAs($admin)
        ->get(route('admin.opnams.index', ['q' => 'OPN-100']))
        ->assertOk()
        ->assertSee('OPN-100')
        ->assertDontSee('OPN-200');

    $this->actingAs($admin)
        ->get(route('admin.opnams.index', ['q' => 'HULL-202']))
        ->assertOk()
        ->assertSee('OPN-200')
        ->assertDontSee('OPN-100');
});

test('filter status hanya menampilkan status terpilih', function () {
    $admin = adminUntukDaftarOpnam();
    $ship = kapalUntukDaftarOpnam('Kapal Satu', 'HULL-001');
    opnamUntukDaftar($admin, $ship, 'OPN-001', Opnam::STATUS_DRAFT, '2026-01-05');
    opnamUntukDaftar($admin, $ship, 'OPN-002', Opnam::STATUS_MENUNGGU_REVIEW, '2026-01-06');

    $this->actingAs($admin)
        ->get(route('admin.opnams.index', ['status' => Opnam::STATUS_MENUNGGU_REVIEW]))
        ->assertOk()
        ->assertSee('OPN-002')
        ->assertDontSee('OPN-001');
});

test('filter tanggal tunggal hanya menampilkan opnam pada tanggal itu', function () {
    $admin = adminUntukDaftarOpnam();
    $ship = kapalUntukDaftarOpnam('Kapal Satu', 'HULL-001');
    opnamUntukDaftar($admin, $ship, 'OPN-001', Opnam::STATUS_DRAFT, '2026-01-05');
    opnamUntukDaftar($admin, $ship, 'OPN-002', Opnam::STATUS_DRAFT, '2026-02-10');

    $this->actingAs($admin)
        ->get(route('admin.opnams.index', ['tanggal' => '2026-02-10']))
        ->assertOk()
        ->assertSee('OPN-002')
        ->assertDontSee('OPN-001');
});

test('filter operator hanya menampilkan opnam milik operator terpilih', function () {
    $admin = adminUntukDaftarOpnam();
    $operatorSatu = User::create([
        'name' => 'Rizky', 'username' => 'rizky', 'email' => 'rizky@example.com',
        'password' => 'password', 'role' => 'petugas', 'is_active' => true,
    ]);
    $operatorDua = User::create([
        'name' => 'Dewi', 'username' => 'dewi', 'email' => 'dewi@example.com',
        'password' => 'password', 'role' => 'petugas', 'is_active' => true,
    ]);
    $ship = kapalUntukDaftarOpnam('Kapal Satu', 'HULL-001');
    opnamUntukDaftar($operatorSatu, $ship, 'OPN-100', Opnam::STATUS_DRAFT, '2026-01-05');
    opnamUntukDaftar($operatorDua, $ship, 'OPN-200', Opnam::STATUS_DRAFT, '2026-01-06');

    $this->actingAs($admin)
        ->get(route('admin.opnams.index', ['operator' => $operatorSatu->id]))
        ->assertOk()
        ->assertSee('OPN-100')
        ->assertDontSee('OPN-200')
        ->assertSee('Semua Operator');
});

test('daftar opnam dipaginasi', function () {
    $admin = adminUntukDaftarOpnam();
    $ship = kapalUntukDaftarOpnam('Kapal Satu', 'HULL-001');

    for ($i = 1; $i <= 16; $i++) {
        opnamUntukDaftar($admin, $ship, sprintf('OPN-%03d', $i), Opnam::STATUS_DRAFT, '2026-01-05');
    }

    $this->actingAs($admin)
        ->get(route('admin.opnams.index'))
        ->assertOk()
        ->assertDontSee('OPN-001');

    $this->actingAs($admin)
        ->get(route('admin.opnams.index', ['page' => 2]))
        ->assertOk()
        ->assertSee('OPN-001');
});

test('admin dapat membuka detail opnam milik petugas', function () {
    $admin = adminUntukDaftarOpnam();
    $petugas = User::create([
        'name' => 'Petugas Satu', 'username' => 'petugas1', 'email' => 'petugas1@example.com',
        'password' => 'password', 'role' => 'petugas', 'is_active' => true,
    ]);
    $ship = kapalUntukDaftarOpnam('Kapal Satu', 'HULL-001');
    $opnam = opnamUntukDaftar($petugas, $ship, 'OPN-001', Opnam::STATUS_DRAFT, '2026-01-05');

    $this->actingAs($admin)
        ->get(route('admin.opnams.show', $opnam))
        ->assertOk()
        ->assertSee('OPN-001')
        ->assertSee('Kembali ke Data Opname');
});
