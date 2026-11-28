<?php

use App\Models\Opnam;
use App\Models\Owner;
use App\Models\Ship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

function policyUser(string $role = 'petugas', string $prefix = 'policy'): User
{
    static $id = 0;
    $id++;

    return User::create([
        'name' => ucfirst($role).' Policy '.$id,
        'username' => $prefix.'-'.$id,
        'email' => $prefix.'-'.$id.'@example.com',
        'password' => 'password',
        'role' => $role,
        'is_active' => true,
    ]);
}

function policyOpnam(User $pemilik, string $status, string $nomor): Opnam
{
    $owner = Owner::create(['name' => 'Owner '.$nomor]);
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

test('pemilik boleh melihat opnamnya', function () {
    $pemilik = policyUser('petugas', 'pemilik-policy');
    $opnam = policyOpnam($pemilik, Opnam::STATUS_DRAFT, 'POL-VIEW-1');

    expect(Gate::forUser($pemilik)->allows('view', $opnam))->toBeTrue()
        ->and($pemilik->can('view', $opnam))->toBeTrue();
});

test('petugas lain tidak boleh melihat opnam milik petugas lain', function () {
    $pemilik = policyUser('petugas', 'pemilik-lain');
    $lain = policyUser('petugas', 'bukan-pemilik');
    $opnam = policyOpnam($pemilik, Opnam::STATUS_DRAFT, 'POL-VIEW-2');

    expect(Gate::forUser($lain)->allows('view', $opnam))->toBeFalse()
        ->and($lain->can('view', $opnam))->toBeFalse();
});

test('admin boleh mengubah opnam berstatus menunggu_review', function () {
    $admin = policyUser('admin', 'admin-update');
    $opnam = policyOpnam($admin, Opnam::STATUS_MENUNGGU_REVIEW, 'POL-ADM-1');

    expect(Gate::forUser($admin)->allows('update', $opnam))->toBeTrue();
});

test('admin tidak boleh mengubah opnam pada status lain', function () {
    $admin = policyUser('admin', 'admin-update-salah');
    $opnam = policyOpnam($admin, Opnam::STATUS_DRAFT, 'POL-ADM-2');

    expect(Gate::forUser($admin)->allows('update', $opnam))->toBeFalse();
});

test('direksi boleh mengubah opnam berstatus menunggu_approval_direksi', function () {
    $direksi = policyUser('direksi', 'direksi-update');
    $opnam = policyOpnam($direksi, Opnam::STATUS_MENUNGGU_APPROVAL_DIREKSI, 'POL-DIR-1');

    expect(Gate::forUser($direksi)->allows('update', $opnam))->toBeTrue();
});

test('direksi tidak boleh mengubah opnam pada status lain', function () {
    $direksi = policyUser('direksi', 'direksi-update-salah');
    $opnam = policyOpnam($direksi, Opnam::STATUS_MENUNGGU_REVIEW, 'POL-DIR-2');

    expect(Gate::forUser($direksi)->allows('update', $opnam))->toBeFalse();
});

test('petugas tidak boleh mengubah opnam walaupun miliknya', function () {
    $petugas = policyUser('petugas', 'petugas-update');
    $opnam = policyOpnam($petugas, Opnam::STATUS_DRAFT, 'POL-PTG-1');

    expect(Gate::forUser($petugas)->allows('update', $opnam))->toBeFalse()
        ->and($petugas->can('update', $opnam))->toBeFalse();
});
