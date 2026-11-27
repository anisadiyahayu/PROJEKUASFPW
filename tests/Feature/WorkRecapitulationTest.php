<?php

use App\Models\Opnam;
use App\Models\Owner;
use App\Models\Ship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function buatUserRekap(string $role, string $username): User
{
    return User::create([
        'name' => ucfirst($role).' Rekap',
        'username' => $username,
        'email' => $username.'@example.com',
        'password' => 'password',
        'role' => $role,
        'is_active' => true,
    ]);
}

function siapkanDataRekap(): array
{
    $admin = buatUserRekap(User::ROLE_ADMIN, 'admin-rekap');
    $owner = Owner::create(['name' => 'PT Rekap Marine']);
    $ship = Ship::create([
        'owner_id' => $owner->id,
        'name' => 'KM Rekap',
        'hull_number' => 'HULL-REKAP-1',
    ]);

    $januari = Opnam::create([
        'opnam_number' => 'OPN-REKAP-JAN',
        'ship_id' => $ship->id,
        'created_by' => $admin->id,
        'status' => Opnam::STATUS_DISETUJUI,
        'tanggal_opnam' => '2026-01-15',
        'total_biaya' => 1000000.00,
    ]);

    $februari = Opnam::create([
        'opnam_number' => 'OPN-REKAP-FEB',
        'ship_id' => $ship->id,
        'created_by' => $admin->id,
        'status' => Opnam::STATUS_DRAFT,
        'tanggal_opnam' => '2026-02-10',
        'total_biaya' => 2500000.00,
    ]);

    return [$admin, $ship, $januari, $februari];
}

test('admin dapat melihat rekapitulasi pada periode tertentu', function () {
    [$admin, , $januari, $februari] = siapkanDataRekap();

    $this->actingAs($admin)
        ->get(route('reports.recapitulation.index', [
            'dari' => '2026-01-01',
            'sampai' => '2026-01-31',
        ]))
        ->assertOk()
        ->assertSee($januari->opnam_number)
        ->assertDontSee($februari->opnam_number)
        ->assertSee('Rp 1.000.000')
        ->assertSee('PT Rekap Marine');
});

test('direksi dapat melihat rekapitulasi', function () {
    [$admin, , $januari] = siapkanDataRekap();
    $direksi = buatUserRekap(User::ROLE_DIREKSI, 'direksi-rekap');

    $this->actingAs($direksi)
        ->get(route('reports.recapitulation.index', [
            'dari' => '2026-01-01',
            'sampai' => '2026-01-31',
        ]))
        ->assertOk()
        ->assertSee($januari->opnam_number);
});

test('rekapitulasi dapat difilter berdasarkan status', function () {
    [$admin, , $januari, $februari] = siapkanDataRekap();

    $this->actingAs($admin)
        ->get(route('reports.recapitulation.index', [
            'dari' => '2026-01-01',
            'sampai' => '2026-02-28',
            'status' => Opnam::STATUS_DISETUJUI,
        ]))
        ->assertOk()
        ->assertSee($januari->opnam_number)
        ->assertDontSee($februari->opnam_number);
});

test('filter periode tidak valid dikembalikan ke periode berjalan', function () {
    [$admin] = siapkanDataRekap();

    $this->actingAs($admin)
        ->get(route('reports.recapitulation.index', [
            'dari' => 'bukan-tanggal',
            'sampai' => '2026-13-40',
        ]))
        ->assertOk();
});

test('admin dapat mengunduh rekapitulasi dalam format pdf', function () {
    [$admin] = siapkanDataRekap();

    $response = $this->actingAs($admin)
        ->get(route('reports.recapitulation.pdf', [
            'dari' => '2026-01-01',
            'sampai' => '2026-01-31',
        ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
    expect($response->headers->get('content-disposition'))->toContain('rekap-pekerjaan-2026-01-01_sd_2026-01-31.pdf');
});

test('admin dapat mengunduh rekapitulasi dalam format excel', function () {
    [$admin] = siapkanDataRekap();

    $response = $this->actingAs($admin)
        ->get(route('reports.recapitulation.excel', [
            'dari' => '2026-01-01',
            'sampai' => '2026-01-31',
        ]));

    $response->assertOk();
    $response->assertHeader(
        'content-type',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    );
    expect($response->headers->get('content-disposition'))->toContain('rekap-pekerjaan-2026-01-01_sd_2026-01-31.xlsx');
});

test('rekapitulasi tetap menampilkan opnam milik kapal yang sudah dihapus', function () {
    [$admin, $ship, $januari] = siapkanDataRekap();
    $ship->delete();

    $this->actingAs($admin)
        ->get(route('reports.recapitulation.index', [
            'dari' => '2026-01-01',
            'sampai' => '2026-01-31',
        ]))
        ->assertOk()
        ->assertSee($januari->opnam_number)
        ->assertSee('KM Rekap');
});

test('petugas tidak boleh mengakses rekapitulasi maupun ekspornya', function () {
    $petugas = buatUserRekap(User::ROLE_PETUGAS, 'petugas-rekap');

    $this->actingAs($petugas)
        ->get(route('reports.recapitulation.index'))
        ->assertForbidden();

    $this->actingAs($petugas)
        ->get(route('reports.recapitulation.pdf'))
        ->assertForbidden();

    $this->actingAs($petugas)
        ->get(route('reports.recapitulation.excel'))
        ->assertForbidden();
});
