<?php

use App\Models\Billing;
use App\Models\Opnam;
use App\Models\Owner;
use App\Models\Ship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function dashboardUser(string $role = 'petugas', string $prefix = 'dash'): User
{
    static $id = 0;
    $id++;

    return User::create([
        'name' => ucfirst($role).' Dashboard '.$id,
        'username' => $prefix.'-'.$id,
        'email' => $prefix.'-'.$id.'@example.com',
        'password' => 'password',
        'role' => $role,
        'is_active' => true,
    ]);
}

function dashboardOpnam(User $pemilik, string $status, string $nomor, ?Owner $owner = null): Opnam
{
    $owner ??= Owner::create(['name' => 'Owner '.$nomor]);
    $ship = Ship::create([
        'owner_id' => $owner->id,
        'name' => 'Kapal '.$nomor,
        'hull_number' => 'HULL-'.$nomor,
    ]);

    return Opnam::create([
        'opnam_number' => $nomor,
        'ship_id' => $ship->id,
        'created_by' => $pemilik->id,
        'status' => $status,
        'total_biaya' => 0,
    ]);
}

test('dashboard petugas menampilkan ringkasan status milik sendiri saja', function () {
    $petugas = dashboardUser('petugas', 'petugas-dash');
    $lain = dashboardUser('petugas', 'petugas-lain');

    dashboardOpnam($petugas, Opnam::STATUS_DRAFT, 'DASH-001');
    dashboardOpnam($petugas, Opnam::STATUS_MENUNGGU_REVIEW, 'DASH-002');
    dashboardOpnam($petugas, Opnam::STATUS_PERLU_REVISI, 'DASH-003');
    dashboardOpnam($petugas, Opnam::STATUS_DISETUJUI, 'DASH-004');
    dashboardOpnam($petugas, Opnam::STATUS_DISETUJUI, 'DASH-005');
    dashboardOpnam($lain, Opnam::STATUS_DRAFT, 'DASH-LAIN');

    $response = $this->actingAs($petugas)->get(route('petugas.dashboard'))->assertOk();

    $response->assertSee('Dashboard Petugas')
        ->assertSee('DASH-001')
        ->assertSee('DASH-005')
        ->assertDontSee('DASH-LAIN')
        ->assertSee(route('petugas.opnams.index'), false);
});

test('dashboard petugas hanya menampilkan lima opnam terbaru miliknya', function () {
    $petugas = dashboardUser('petugas', 'petugas-top');
    $lain = dashboardUser('petugas', 'petugas-top-lain');

    for ($i = 1; $i <= 6; $i++) {
        dashboardOpnam($petugas, Opnam::STATUS_DRAFT, sprintf('TOP-%03d', $i));
    }
    dashboardOpnam($lain, Opnam::STATUS_DRAFT, 'TOP-LAIN');

    $response = $this->actingAs($petugas)->get(route('petugas.dashboard'))->assertOk();

    foreach (['TOP-006', 'TOP-005', 'TOP-004', 'TOP-003', 'TOP-002'] as $nomor) {
        $response->assertSee($nomor);
    }
    $response->assertDontSee('TOP-001')
        ->assertDontSee('TOP-LAIN');
});

test('petugas dapat membuka detail opnam miliknya dan tombol kembali ke daftar petugas', function () {
    $petugas = dashboardUser('petugas', 'petugas-detail');
    $opnam = dashboardOpnam($petugas, Opnam::STATUS_DRAFT, 'DETAIL-001');

    $this->actingAs($petugas)
        ->get(route('petugas.opnams.show', $opnam))
        ->assertOk()
        ->assertSee($opnam->opnam_number)
        ->assertSee(route('petugas.opnams.index'), false);
});

test('petugas mendapat 403 saat membuka opnam milik petugas lain', function () {
    $petugas = dashboardUser('petugas', 'petugas-akses');
    $lain = dashboardUser('petugas', 'petugas-akses-lain');
    $opnam = dashboardOpnam($lain, Opnam::STATUS_DRAFT, 'AKSES-LAIN');

    $this->actingAs($petugas)
        ->get(route('petugas.opnams.show', $opnam))
        ->assertForbidden();
});

test('dashboard admin menampilkan antrean review dan jumlah pengguna aktif nonaktif', function () {
    $admin = dashboardUser('admin', 'admin-dash');
    $petugas = dashboardUser('petugas', 'admin-petugas');
    $nonaktif = dashboardUser('petugas', 'admin-nonaktif');
    $nonaktif->update(['is_active' => false]);

    dashboardOpnam($petugas, Opnam::STATUS_MENUNGGU_REVIEW, 'ADM-001');
    dashboardOpnam($petugas, Opnam::STATUS_PERLU_REVISI, 'ADM-002');
    dashboardOpnam($petugas, Opnam::STATUS_DISETUJUI, 'ADM-003');
    dashboardOpnam($petugas, Opnam::STATUS_DRAFT, 'ADM-DRAFT');

    $this->actingAs($admin)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Dashboard Admin')
        ->assertSee('Buka Review Opname')
        ->assertSee('Buka Daftar Invoice')
        ->assertSee('Buka Data Opname')
        ->assertSee((string) $nonaktif->id);
});

test('dashboard direksi menampilkan jumlah dan total tagihan menunggu approval', function () {
    $direksi = dashboardUser('direksi', 'direksi-dash');
    $petugas = dashboardUser('petugas', 'direksi-petugas');

    $menunggu1 = dashboardOpnam($petugas, Opnam::STATUS_MENUNGGU_APPROVAL_DIREKSI, 'DIR-001');
    $menunggu2 = dashboardOpnam($petugas, Opnam::STATUS_MENUNGGU_APPROVAL_DIREKSI, 'DIR-002');
    $disetujui = dashboardOpnam($petugas, Opnam::STATUS_DISETUJUI, 'DIR-003');

    Billing::create(['opnam_id' => $menunggu1->id, 'status' => Billing::STATUS_MENUNGGU_APPROVAL, 'total_amount' => 1250]);
    Billing::create(['opnam_id' => $menunggu2->id, 'status' => Billing::STATUS_MENUNGGU_APPROVAL, 'total_amount' => 7750]);
    Billing::create(['opnam_id' => $disetujui->id, 'status' => Billing::STATUS_DISETUJUI, 'total_amount' => 999999]);

    $this->actingAs($direksi)->get(route('direksi.dashboard'))
        ->assertOk()
        ->assertSee('Dashboard Direksi')
        ->assertSee('Buka Daftar Tagihan')
        ->assertSee('Buka Daftar Dokumen')
        ->assertSee('Rp 9.000')
        ->assertDontSee('Rp 1.008.999');
});

test('tamu dialihkan ke login dari dashboard', function () {
    $this->get(route('petugas.dashboard'))->assertRedirect(route('login'));
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    $this->get(route('direksi.dashboard'))->assertRedirect(route('login'));
});

test('role salah tidak dapat membuka dashboard role lain', function () {
    $petugas = dashboardUser('petugas', 'petugas-guard');

    $this->actingAs($petugas)->get(route('admin.dashboard'))->assertForbidden();
    $this->actingAs($petugas)->get(route('direksi.dashboard'))->assertForbidden();
});
