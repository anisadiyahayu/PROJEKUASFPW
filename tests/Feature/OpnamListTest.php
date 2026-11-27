<?php

<<<<<<< HEAD
use App\Models\Opnam;
use App\Models\Owner;
use App\Models\Ship;
use App\Models\User;
=======
use App\Models\MaterialSpec;
use App\Models\Opnam;
use App\Models\OpnamItem;
use App\Models\Owner;
use App\Models\Ship;
use App\Models\Tariff;
use App\Models\Unit;
use App\Models\User;
use App\Models\WorkCategory;
use App\Models\WorkType;
>>>>>>> d84cf2129cb7286d690c39c169d2bc78fcf194c3
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

<<<<<<< HEAD
test('filter tanggal hanya menampilkan opnam dalam rentang', function () {
=======
test('filter tanggal hanya menampilkan opnam pada tanggal yang dipilih', function () {
>>>>>>> d84cf2129cb7286d690c39c169d2bc78fcf194c3
    $admin = adminUntukDaftarOpnam();
    $ship = kapalUntukDaftarOpnam('Kapal Satu', 'HULL-001');
    opnamUntukDaftar($admin, $ship, 'OPN-001', Opnam::STATUS_DRAFT, '2026-01-05');
    opnamUntukDaftar($admin, $ship, 'OPN-002', Opnam::STATUS_DRAFT, '2026-02-10');

    $this->actingAs($admin)
<<<<<<< HEAD
        ->get(route('admin.opnams.index', [
            'tanggal_dari' => '2026-02-01',
            'tanggal_sampai' => '2026-02-28',
        ]))
=======
        ->get(route('admin.opnams.index', ['tanggal' => '2026-02-10']))
>>>>>>> d84cf2129cb7286d690c39c169d2bc78fcf194c3
        ->assertOk()
        ->assertSee('OPN-002')
        ->assertDontSee('OPN-001');
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
<<<<<<< HEAD
=======

test('filter operator hanya menampilkan opnam milik petugas terpilih', function () {
    $admin = adminUntukDaftarOpnam();
    $petugasSatu = User::create([
        'name' => 'Petugas Satu', 'username' => uniqid('petugas-satu-'),
        'email' => uniqid('satu-').'@example.com', 'password' => 'password',
        'role' => 'petugas', 'is_active' => true,
    ]);
    $petugasDua = User::create([
        'name' => 'Petugas Dua', 'username' => uniqid('petugas-dua-'),
        'email' => uniqid('dua-').'@example.com', 'password' => 'password',
        'role' => 'petugas', 'is_active' => true,
    ]);
    $ship = kapalUntukDaftarOpnam('Kapal Satu', 'HULL-101');
    opnamUntukDaftar($petugasSatu, $ship, 'OPN-101', Opnam::STATUS_DRAFT, '2026-01-05');
    opnamUntukDaftar($petugasDua, $ship, 'OPN-202', Opnam::STATUS_DRAFT, '2026-01-06');

    $this->actingAs($admin)
        ->get(route('admin.opnams.index', ['operator' => $petugasSatu->id]))
        ->assertOk()
        ->assertSee('OPN-101')
        ->assertDontSee('OPN-202');

    $this->actingAs($admin)
        ->get(route('admin.opnams.index'))
        ->assertOk()
        ->assertSee('Petugas Satu')
        ->assertSee('Petugas Dua');
});

test('admin dapat membuka detail opnam dari tombol mata', function () {
    $admin = adminUntukDaftarOpnam();
    $ship = kapalUntukDaftarOpnam('Kapal Detail', 'HULL-DETAIL');
    $opnam = opnamUntukDaftar($admin, $ship, 'OPN-DETAIL', Opnam::STATUS_DRAFT, '2026-01-05');

    $this->actingAs($admin)
        ->get(route('admin.opnams.index'))
        ->assertOk()
        ->assertSee(route('admin.opnams.show', $opnam), false);

    $this->actingAs($admin)
        ->get(route('admin.opnams.show', $opnam))
        ->assertOk()
        ->assertSee('OPN-DETAIL')
        ->assertSee('Kapal Detail');

    $petugas = User::create([
        'name' => 'Petugas Detail', 'username' => uniqid('petugas-detail-'),
        'email' => uniqid('detail-').'@example.com', 'password' => 'password',
        'role' => 'petugas', 'is_active' => true,
    ]);
    $this->actingAs($petugas)
        ->get(route('admin.opnams.show', $opnam))
        ->assertForbidden();
});

test('detail opnam menampilkan lokasi pekerjaan dari item', function () {
    $admin = adminUntukDaftarOpnam();
    $ship = kapalUntukDaftarOpnam('Kapal Lokasi', 'HULL-LOK');
    $opnam = opnamUntukDaftar($admin, $ship, 'OPN-LOK', Opnam::STATUS_DRAFT, '2026-01-05');

    $category = WorkCategory::create(['name' => 'Kategori Lokasi']);
    $type = WorkType::create(['work_category_id' => $category->id, 'name' => 'Jenis Lokasi']);
    $material = MaterialSpec::create(['work_type_id' => $type->id, 'name' => 'Material Lokasi']);
    $unit = Unit::create(['code' => 'LOK-M2', 'name' => 'm²']);
    $tariff = Tariff::create([
        'material_spec_id' => $material->id, 'unit_id' => $unit->id, 'created_by' => $admin->id,
        'price' => 1000, 'effective_from' => '2026-01-01',
    ]);
    OpnamItem::create([
        'opnam_id' => $opnam->id, 'tariff_id' => $tariff->id,
        'volume' => 1, 'unit_price' => 1000, 'subtotal' => 1000,
        'description' => 'Pekerjaan di dock', 'sort_order' => 1,
        'lokasi' => 'Dock 02 — Floating Dock',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.opnams.show', $opnam))
        ->assertOk()
        ->assertSee('Dock 02 — Floating Dock', false);
});
>>>>>>> d84cf2129cb7286d690c39c169d2bc78fcf194c3
