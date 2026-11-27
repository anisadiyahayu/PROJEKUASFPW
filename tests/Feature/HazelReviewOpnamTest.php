<?php

use App\Models\ActivityLog;
use App\Models\Billing;
use App\Models\Opnam;
use App\Models\Owner;
use App\Models\Ship;
use App\Models\StatusHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function hazelBuatUser(string $role, string $username): User
{
    return User::create([
        'name' => ucfirst($role),
        'username' => $username,
        'email' => $username.'@example.com',
        'password' => 'password',
        'role' => $role,
        'is_active' => true,
    ]);
}

function hazelBuatOpnam(User $creator, string $number, string $status): Opnam
{
    $owner = Owner::create(['name' => 'Owner '.$number]);
    $ship = Ship::create([
        'owner_id' => $owner->id,
        'name' => 'Kapal '.$number,
        'hull_number' => 'HL-'.$number,
    ]);

    return Opnam::create([
        'opnam_number' => $number,
        'ship_id' => $ship->id,
        'created_by' => $creator->id,
        'status' => $status,
        'tanggal_opnam' => '2026-11-20',
        'total_biaya' => 1250000,
    ]);
}

function hazelBuatTagihan(Opnam $opnam, string $status = Billing::STATUS_MENUNGGU_APPROVAL): Billing
{
    $billing = Billing::create([
        'opnam_id' => $opnam->id,
        'status' => $status,
        'subtotal_amount' => $opnam->total_biaya,
        'adjustment_amount' => 0,
        'total_amount' => $opnam->total_biaya,
    ]);
    $billing->update(['billing_number' => sprintf('BLG-%06d', $billing->id)]);

    $opnam->update(['status' => Opnam::STATUS_MENUNGGU_APPROVAL_DIREKSI]);

    return $billing;
}

test('halaman review hanya dapat diakses Admin dan hanya menampilkan opnam menunggu review', function () {
    $admin = hazelBuatUser('admin', 'admin-review');
    $direksi = hazelBuatUser('direksi', 'direksi-review');
    $menunggu = hazelBuatOpnam($admin, 'OPN-RV-001', Opnam::STATUS_MENUNGGU_REVIEW);
    $selesai = hazelBuatOpnam($admin, 'OPN-RV-002', Opnam::STATUS_DISETUJUI);

    $this->actingAs($direksi)
        ->get(route('admin.review-opnams.index'))
        ->assertForbidden();

    $this->actingAs($admin)
        ->get(route('admin.review-opnams.index'))
        ->assertOk()
        ->assertSee('OPN-RV-001')
        ->assertDontSee('OPN-RV-002');

    $this->actingAs($admin)
        ->get(route('admin.review-opnams.show', $menunggu))
        ->assertOk()
        ->assertSee('Rincian Pekerjaan');

    $this->actingAs($admin)
        ->get(route('admin.review-opnams.show', $selesai))
        ->assertStatus(422);
});

test('Admin meneruskan opnam dan tagihan dibuat otomatis', function () {
    $admin = hazelBuatUser('admin', 'admin-forward');
    $opnam = hazelBuatOpnam($admin, 'OPN-RV-010', Opnam::STATUS_MENUNGGU_REVIEW);

    $this->actingAs($admin)
        ->post(route('admin.review-opnams.approve', $opnam))
        ->assertRedirect();

    $opnam->refresh();
    expect($opnam->status)->toBe(Opnam::STATUS_MENUNGGU_APPROVAL_DIREKSI)
        ->and($opnam->forwarded_at)->not->toBeNull();

    $billing = Billing::where('opnam_id', $opnam->id)->firstOrFail();
    expect($billing->status)->toBe(Billing::STATUS_MENUNGGU_APPROVAL)
        ->and((float) $billing->subtotal_amount)->toBe(1250000.0)
        ->and((float) $billing->total_amount)->toBe(1250000.0)
        ->and($billing->billing_number)->toStartWith('BLG-');

    expect(StatusHistory::where('statusable_type', $opnam->getMorphClass())
        ->where('statusable_id', $opnam->id)
        ->where('to_status', Opnam::STATUS_MENUNGGU_APPROVAL_DIREKSI)
        ->exists())->toBeTrue();

    expect(ActivityLog::where('event', 'AD-08 Setujui Hasil Review')->exists())->toBeTrue();
});

test('Admin mengembalikan opnam untuk revisi dengan catatan wajib', function () {
    $admin = hazelBuatUser('admin', 'admin-return');
    $opnam = hazelBuatOpnam($admin, 'OPN-RV-020', Opnam::STATUS_MENUNGGU_REVIEW);

    $this->actingAs($admin)
        ->post(route('admin.review-opnams.return-for-revision', $opnam), [])
        ->assertSessionHasErrors('catatan');

    $this->actingAs($admin)
        ->post(route('admin.review-opnams.return-for-revision', $opnam), [
            'catatan' => 'Lengkapi bukti pekerjaan pada bagian lambung.',
        ])
        ->assertRedirect();

    $opnam->refresh();
    expect($opnam->status)->toBe(Opnam::STATUS_PERLU_REVISI);

    $riwayat = StatusHistory::where('statusable_type', $opnam->getMorphClass())
        ->where('statusable_id', $opnam->id)
        ->where('to_status', Opnam::STATUS_PERLU_REVISI)
        ->firstOrFail();
    expect($riwayat->note)->toBe('Lengkapi bukti pekerjaan pada bagian lambung.');
});

test('Direksi hanya melihat tagihan berstatus menunggu approval', function () {
    $direksi = hazelBuatUser('direksi', 'direksi-tagihan');
    $admin = hazelBuatUser('admin', 'admin-tagihan');
    $menunggu = hazelBuatTagihan(hazelBuatOpnam($admin, 'OPN-TG-001', Opnam::STATUS_MENUNGGU_APPROVAL_DIREKSI));
    $disetujui = hazelBuatTagihan(
        hazelBuatOpnam($admin, 'OPN-TG-002', Opnam::STATUS_MENUNGGU_APPROVAL_DIREKSI),
        Billing::STATUS_DISETUJUI,
    );

    $this->actingAs($admin)
        ->get(route('direksi.tagihans.index'))
        ->assertForbidden();

    $this->actingAs($direksi)
        ->get(route('direksi.tagihans.index'))
        ->assertOk()
        ->assertSee($menunggu->billing_number)
        ->assertDontSee($disetujui->billing_number);

    $this->actingAs($direksi)
        ->get(route('direksi.tagihans.show', $menunggu))
        ->assertOk()
        ->assertSee('Keputusan');

    $this->actingAs($direksi)
        ->get(route('direksi.tagihans.show', $disetujui))
        ->assertStatus(422);
});
