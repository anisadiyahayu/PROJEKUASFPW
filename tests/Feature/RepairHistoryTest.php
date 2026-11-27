<?php

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
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function buatUserRiwayat(string $role, string $username): User
{
    return User::create([
        'name' => ucfirst($role).' Riwayat',
        'username' => $username,
        'email' => $username.'@example.com',
        'password' => 'password',
        'role' => $role,
        'is_active' => true,
    ]);
}

function buatKapalRiwayat(string $nama, string $hull): Ship
{
    $owner = Owner::create(['name' => 'Pemilik '.$hull]);

    return Ship::create([
        'owner_id' => $owner->id,
        'name' => $nama,
        'hull_number' => $hull,
    ]);
}

function buatOpnamRiwayat(User $creator, Ship $ship, string $number, string $status, string $tanggal): Opnam
{
    return Opnam::create([
        'opnam_number' => $number,
        'ship_id' => $ship->id,
        'created_by' => $creator->id,
        'status' => $status,
        'tanggal_opnam' => $tanggal,
        'total_biaya' => 1250000.00,
    ]);
}

test('admin melihat seluruh kapal yang memiliki riwayat perbaikan', function () {
    $admin = buatUserRiwayat(User::ROLE_ADMIN, 'admin-riwayat');
    $ship = buatKapalRiwayat('KM Riwayat Admin', 'HULL-RIW-1');
    buatOpnamRiwayat($admin, $ship, 'OPN-RIW-1', Opnam::STATUS_DRAFT, '2026-01-10');

    $this->actingAs($admin)
        ->get(route('repair-history.index'))
        ->assertOk()
        ->assertSee('KM Riwayat Admin')
        ->assertSee('HULL-RIW-1')
        ->assertSee('Lihat Riwayat');
});

test('direksi hanya melihat kapal dengan opnam disetujui atau dokumen terbit', function () {
    $admin = buatUserRiwayat(User::ROLE_ADMIN, 'admin-riwayat');
    $direksi = buatUserRiwayat(User::ROLE_DIREKSI, 'direksi-riwayat');

    $shipDraft = buatKapalRiwayat('KM Masih Draft', 'HULL-RIW-DRAFT');
    buatOpnamRiwayat($admin, $shipDraft, 'OPN-RIW-DRAFT', Opnam::STATUS_DRAFT, '2026-01-10');

    $shipDisetujui = buatKapalRiwayat('KM Sudah Disetujui', 'HULL-RIW-OK');
    buatOpnamRiwayat($admin, $shipDisetujui, 'OPN-RIW-OK', Opnam::STATUS_DISETUJUI, '2026-01-11');

    $this->actingAs($direksi)
        ->get(route('repair-history.index'))
        ->assertOk()
        ->assertSee('KM Sudah Disetujui')
        ->assertDontSee('KM Masih Draft');
});

test('pencarian riwayat perbaikan mencocokkan nama kapal dan nomor lambung', function () {
    $admin = buatUserRiwayat(User::ROLE_ADMIN, 'admin-riwayat');
    $shipAlfa = buatKapalRiwayat('KM Alfa', 'HULL-CARI-ALFA');
    $shipBeta = buatKapalRiwayat('KM Beta', 'HULL-CARI-BETA');
    buatOpnamRiwayat($admin, $shipAlfa, 'OPN-CARI-1', Opnam::STATUS_DRAFT, '2026-01-10');
    buatOpnamRiwayat($admin, $shipBeta, 'OPN-CARI-2', Opnam::STATUS_DRAFT, '2026-01-11');

    $this->actingAs($admin)
        ->get(route('repair-history.index', ['q' => 'Alfa']))
        ->assertOk()
        ->assertSee('KM Alfa')
        ->assertDontSee('KM Beta');

    $this->actingAs($admin)
        ->get(route('repair-history.index', ['q' => 'HULL-CARI-BETA']))
        ->assertOk()
        ->assertSee('KM Beta')
        ->assertDontSee('KM Alfa');
});

test('halaman detail menampilkan opnam kapal sesuai status', function () {
    $admin = buatUserRiwayat(User::ROLE_ADMIN, 'admin-riwayat');
    $ship = buatKapalRiwayat('KM Detail', 'HULL-DETAIL-1');
    buatOpnamRiwayat($admin, $ship, 'OPN-DETAIL-DRAFT', Opnam::STATUS_DRAFT, '2026-01-10');
    buatOpnamRiwayat($admin, $ship, 'OPN-DETAIL-OK', Opnam::STATUS_DISETUJUI, '2026-01-11');

    $this->actingAs($admin)
        ->get(route('repair-history.show', $ship))
        ->assertOk()
        ->assertSee('OPN-DETAIL-DRAFT')
        ->assertSee('OPN-DETAIL-OK')
        ->assertSee('Rp 1.250.000');

    $this->actingAs($admin)
        ->get(route('repair-history.show', ['ship' => $ship, 'status' => Opnam::STATUS_DISETUJUI]))
        ->assertOk()
        ->assertSee('OPN-DETAIL-OK')
        ->assertDontSee('OPN-DETAIL-DRAFT');
});

test('direksi tidak melihat opnam draft pada detail riwayat kapal', function () {
    $admin = buatUserRiwayat(User::ROLE_ADMIN, 'admin-riwayat');
    $direksi = buatUserRiwayat(User::ROLE_DIREKSI, 'direksi-riwayat');
    $ship = buatKapalRiwayat('KM Detail Direksi', 'HULL-DETAIL-2');
    buatOpnamRiwayat($admin, $ship, 'OPN-DIR-DRAFT', Opnam::STATUS_DRAFT, '2026-01-10');
    buatOpnamRiwayat($admin, $ship, 'OPN-DIR-OK', Opnam::STATUS_DOKUMEN_TERBIT, '2026-01-11');

    $this->actingAs($direksi)
        ->get(route('repair-history.show', $ship))
        ->assertOk()
        ->assertSee('OPN-DIR-OK')
        ->assertDontSee('OPN-DIR-DRAFT');
});

test('detail riwayat menampilkan item pekerjaan dari snapshot', function () {
    $admin = buatUserRiwayat(User::ROLE_ADMIN, 'admin-riwayat');
    $ship = buatKapalRiwayat('KM Item', 'HULL-ITEM-1');
    $opnam = buatOpnamRiwayat($admin, $ship, 'OPN-ITEM-1', Opnam::STATUS_DISETUJUI, '2026-01-10');

    $category = WorkCategory::create(['name' => 'Pengecatan']);
    $type = WorkType::create(['work_category_id' => $category->id, 'name' => 'Cat Lambung']);
    $spec = MaterialSpec::create(['work_type_id' => $type->id, 'name' => 'Epoxy']);
    $unit = Unit::create(['code' => 'M2', 'name' => 'Meter Persegi']);
    $tariff = Tariff::create([
        'material_spec_id' => $spec->id,
        'unit_id' => $unit->id,
        'created_by' => $admin->id,
        'price' => 75000,
        'effective_from' => '2026-01-01',
    ]);

    OpnamItem::create([
        'opnam_id' => $opnam->id,
        'tariff_id' => $tariff->id,
        'volume' => 2,
        'unit_price' => 75000,
        'subtotal' => 150000,
        'description' => 'Pengecatan lambung kiri',
        'sort_order' => 1,
    ]);

    $this->actingAs($admin)
        ->get(route('repair-history.show', $ship))
        ->assertOk()
        ->assertSee('Cat Lambung')
        ->assertSee('Epoxy')
        ->assertSee('Meter Persegi')
        ->assertSee('Pengecatan lambung kiri')
        ->assertSee('Rp 150.000');
});

test('riwayat kapal yang sudah dihapus tetap dapat dilihat', function () {
    $admin = buatUserRiwayat(User::ROLE_ADMIN, 'admin-riwayat');
    $ship = buatKapalRiwayat('KM Terhapus', 'HULL-TERHAPUS-1');
    $opnam = buatOpnamRiwayat($admin, $ship, 'OPN-TERHAPUS-1', Opnam::STATUS_DISETUJUI, '2026-01-10');
    $ship->delete();

    $this->actingAs($admin)
        ->get(route('repair-history.index'))
        ->assertOk()
        ->assertSee('KM Terhapus')
        ->assertSee('Dihapus');

    $this->actingAs($admin)
        ->get(route('repair-history.show', $ship))
        ->assertOk()
        ->assertSee($opnam->opnam_number)
        ->assertSee('sudah dihapus dari data aktif');
});

test('petugas tidak boleh mengakses riwayat perbaikan kapal', function () {
    $petugas = buatUserRiwayat(User::ROLE_PETUGAS, 'petugas-riwayat');

    $this->actingAs($petugas)
        ->get(route('repair-history.index'))
        ->assertForbidden();
});
