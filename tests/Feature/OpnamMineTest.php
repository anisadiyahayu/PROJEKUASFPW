<?php

use App\Models\Opnam;
use App\Models\Owner;
use App\Models\Ship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function siapkanOpnameSaya(): array
{
    $petugas = User::create([
        'name' => 'Petugas Satu', 'username' => 'petugas1', 'email' => 'petugas1@example.com',
        'password' => 'password', 'role' => 'petugas', 'is_active' => true,
    ]);
    $petugasLain = User::create([
        'name' => 'Petugas Dua', 'username' => 'petugas2', 'email' => 'petugas2@example.com',
        'password' => 'password', 'role' => 'petugas', 'is_active' => true,
    ]);
    $owner = Owner::create(['name' => 'PT Bahari Sentosa']);
    $ship = Ship::create([
        'owner_id' => $owner->id, 'name' => 'KM Samudra Jaya',
        'hull_number' => 'SJ-0824', 'jenis_kapal' => 'Kapal Kargo',
    ]);

    return compact('petugas', 'petugasLain', 'ship');
}

function buatOpnamSaya(User $creator, Ship $ship, string $number, string $status, string $tanggal): Opnam
{
    return Opnam::create([
        'opnam_number' => $number,
        'ship_id' => $ship->id,
        'created_by' => $creator->id,
        'status' => $status,
        'tanggal_opnam' => $tanggal,
<<<<<<< HEAD
        'lokasi' => 'Dock 01 — Graving Dock',
=======
>>>>>>> d84cf2129cb7286d690c39c169d2bc78fcf194c3
        'total_biaya' => 1000000,
    ]);
}

test('opname saya hanya menampilkan opnam milik petugas yang login', function () {
    $data = siapkanOpnameSaya();
    buatOpnamSaya($data['petugas'], $data['ship'], 'OPN-001', Opnam::STATUS_DRAFT, '2026-06-25');
    buatOpnamSaya($data['petugasLain'], $data['ship'], 'OPN-999', Opnam::STATUS_DRAFT, '2026-06-26');

    $this->actingAs($data['petugas'])
        ->get(route('petugas.opnams.index'))
        ->assertOk()
        ->assertSee('OPN-001')
        ->assertDontSee('OPN-999')
        ->assertSee('Buat Opname');
});

test('opname saya mendukung pencarian dan filter status', function () {
    $data = siapkanOpnameSaya();
    buatOpnamSaya($data['petugas'], $data['ship'], 'OPN-100', Opnam::STATUS_DRAFT, '2026-06-25');
    buatOpnamSaya($data['petugas'], $data['ship'], 'OPN-200', Opnam::STATUS_MENUNGGU_REVIEW, '2026-06-26');

    $this->actingAs($data['petugas'])
        ->get(route('petugas.opnams.index', ['q' => 'OPN-100']))
        ->assertOk()
        ->assertSee('OPN-100')
        ->assertDontSee('OPN-200');

    $this->actingAs($data['petugas'])
        ->get(route('petugas.opnams.index', ['status' => Opnam::STATUS_MENUNGGU_REVIEW]))
        ->assertOk()
        ->assertSee('OPN-200')
        ->assertDontSee('OPN-100');
});

test('opname saya dapat difilter dengan satu tanggal', function () {
    $data = siapkanOpnameSaya();
    buatOpnamSaya($data['petugas'], $data['ship'], 'OPN-100', Opnam::STATUS_DRAFT, '2026-06-25');
    buatOpnamSaya($data['petugas'], $data['ship'], 'OPN-200', Opnam::STATUS_DRAFT, '2026-06-26');

    $this->actingAs($data['petugas'])
        ->get(route('petugas.opnams.index', ['tanggal' => '2026-06-26']))
        ->assertOk()
        ->assertSee('OPN-200')
        ->assertDontSee('OPN-100');
});

test('opname saya dipaginasi', function () {
    $data = siapkanOpnameSaya();
    for ($i = 1; $i <= 16; $i++) {
        buatOpnamSaya($data['petugas'], $data['ship'], sprintf('OPN-%03d', $i), Opnam::STATUS_DRAFT, '2026-06-25');
    }

    $this->actingAs($data['petugas'])
        ->get(route('petugas.opnams.index'))
        ->assertOk()
        ->assertDontSee('OPN-001');

    $this->actingAs($data['petugas'])
        ->get(route('petugas.opnams.index', ['page' => 2]))
        ->assertOk()
        ->assertSee('OPN-001');
});
