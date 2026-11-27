<?php

use App\Models\ActivityLog;
use App\Models\Billing;
use App\Models\BillingAdjustment;
use App\Models\MaterialSpec;
use App\Models\Opnam;
use App\Models\Owner;
use App\Models\Ship;
use App\Models\StatusHistory;
use App\Models\Tariff;
use App\Models\Unit;
use App\Models\User;
use App\Models\WorkCategory;
use App\Models\WorkType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function hazelRevisiBuatUser(string $role, string $username): User
{
    return User::create([
        'name' => ucfirst($role).' Revisi',
        'username' => $username,
        'email' => $username.'@example.com',
        'password' => 'password',
        'role' => $role,
        'is_active' => true,
    ]);
}

function hazelRevisiBuatOpnam(User $creator, string $number, string $status): Opnam
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
        'catatan' => 'Catatan awal',
        'total_biaya' => 0,
    ]);
}

function hazelRevisiBuatTagihan(Opnam $opnam): Billing
{
    $billing = Billing::create([
        'opnam_id' => $opnam->id,
        'status' => Billing::STATUS_MENUNGGU_APPROVAL,
        'subtotal_amount' => $opnam->total_biaya,
        'adjustment_amount' => 0,
        'total_amount' => $opnam->total_biaya,
    ]);
    $billing->update(['billing_number' => sprintf('BLG-%06d', $billing->id)]);

    return $billing;
}

/**
 * @return array{category: WorkCategory, type: WorkType, spec: MaterialSpec, unit: Unit, tariff: Tariff}
 */
function hazelRevisiBuatTarif(User $creator): array
{
    $category = WorkCategory::create(['name' => 'Kategori Revisi']);
    $type = WorkType::create(['work_category_id' => $category->id, 'name' => 'Jenis Revisi']);
    $spec = MaterialSpec::create(['work_type_id' => $type->id, 'name' => 'Material Revisi']);
    $unit = Unit::create(['code' => 'M2-REV', 'name' => 'Meter Persegi Revisi']);
    $tariff = Tariff::create([
        'material_spec_id' => $spec->id,
        'unit_id' => $unit->id,
        'created_by' => $creator->id,
        'price' => 100000,
        'effective_from' => '2026-01-01',
    ]);

    return compact('category', 'type', 'spec', 'unit', 'tariff');
}

/**
 * @param  array{category: WorkCategory, type: WorkType, spec: MaterialSpec, unit: Unit, tariff: Tariff}  $tarif
 * @return array<string, mixed>
 */
function hazelRevisiPayload(array $tarif, float|int $volume = 10, string $kompleksitas = 'low'): array
{
    return [
        'work_category_id' => $tarif['category']->id,
        'work_type_id' => $tarif['type']->id,
        'material_spec_id' => $tarif['spec']->id,
        'unit_id' => $tarif['unit']->id,
        'volume' => $volume,
        'ketebalan' => null,
        'kompleksitas' => $kompleksitas,
        'catatan_tambahan' => null,
        'description' => 'Pekerjaan revisi',
    ];
}

test('halaman revisi hanya dapat diakses Admin pada status menunggu review', function () {
    $admin = hazelRevisiBuatUser('admin', 'admin-halaman-revisi');
    $direksi = hazelRevisiBuatUser('direksi', 'direksi-halaman-revisi');
    $menunggu = hazelRevisiBuatOpnam($admin, 'OPN-REV-HAL-001', Opnam::STATUS_MENUNGGU_REVIEW);
    $selesai = hazelRevisiBuatOpnam($admin, 'OPN-REV-HAL-002', Opnam::STATUS_DISETUJUI);

    $this->actingAs($direksi)
        ->get(route('admin.review-opnams.revisi', $menunggu))
        ->assertForbidden();

    $this->actingAs($admin)
        ->get(route('admin.review-opnams.revisi', $menunggu))
        ->assertOk()
        ->assertSee('Revisi '.$menunggu->opnam_number)
        ->assertSee('Simpan Data Opnam');

    $this->actingAs($admin)
        ->get(route('admin.review-opnams.revisi', $selesai))
        ->assertStatus(422);
});

test('Admin merevisi data dan item opnam dengan kalkulasi serta log perubahan', function () {
    $admin = hazelRevisiBuatUser('admin', 'admin-revisi-data');
    $opnam = hazelRevisiBuatOpnam($admin, 'OPN-REV-ADM-001', Opnam::STATUS_MENUNGGU_REVIEW);
    $tarif = hazelRevisiBuatTarif($admin);

    $this->actingAs($admin)
        ->post(route('admin.review-opnams.items.store', $opnam), hazelRevisiPayload($tarif, 10, 'medium'))
        ->assertRedirect();

    $item = $opnam->items()->firstOrFail();
    expect((float) $item->unit_price)->toBe(100000.0)
        ->and((float) $item->faktor)->toBe(1.1)
        ->and((float) $item->subtotal)->toBe(1100000.0)
        ->and((float) $opnam->fresh()->total_biaya)->toBe(1100000.0)
        ->and($opnam->fresh()->status)->toBe(Opnam::STATUS_MENUNGGU_REVIEW);

    $this->actingAs($admin)
        ->put(route('admin.review-opnams.update-revisi', $opnam), [
            'tanggal_opnam' => '2026-11-21',
            'catatan' => 'Catatan setelah revisi Admin.',
        ])
        ->assertRedirect();

    $opnam->refresh();
    expect($opnam->tanggal_opnam->toDateString())->toBe('2026-11-21')
        ->and($opnam->catatan)->toBe('Catatan setelah revisi Admin.');

    $logItem = ActivityLog::where('event', 'AD-07 Revisi Data Opnam')
        ->where('subject_id', $opnam->id)
        ->oldest('id')
        ->firstOrFail();
    expect((float) $logItem->new_values['item']['subtotal'])->toBe(1100000.0)
        ->and($logItem->old_values['item'])->toBeNull();

    $logOpnam = ActivityLog::where('event', 'AD-07 Revisi Data Opnam')
        ->where('subject_id', $opnam->id)
        ->latest('id')
        ->firstOrFail();
    expect($logOpnam->old_values['catatan'])->toBe('Catatan awal')
        ->and($logOpnam->new_values['catatan'])->toBe('Catatan setelah revisi Admin.')
        ->and((float) $logOpnam->new_values['total_biaya'])->toBe(1100000.0);

    expect(StatusHistory::where('statusable_type', $opnam->getMorphClass())
        ->where('statusable_id', $opnam->id)->exists())->toBeFalse();
});

test('Admin mengubah volume tanpa mencari ulang tarif master dan validasi ditolak', function () {
    $admin = hazelRevisiBuatUser('admin', 'admin-revisi-volume');
    $opnam = hazelRevisiBuatOpnam($admin, 'OPN-REV-ADM-002', Opnam::STATUS_MENUNGGU_REVIEW);
    $tarif = hazelRevisiBuatTarif($admin);

    $this->actingAs($admin)
        ->post(route('admin.review-opnams.items.store', $opnam), hazelRevisiPayload($tarif))
        ->assertRedirect();

    $item = $opnam->items()->firstOrFail();
    $tarif['tariff']->update(['price' => 150000]);

    $payload = hazelRevisiPayload($tarif, 12);
    $payload['kompleksitas'] = 'tidak-ada';
    $this->actingAs($admin)
        ->put(route('admin.review-opnams.items.update', [$opnam, $item]), $payload)
        ->assertSessionHasErrors('kompleksitas');

    $this->actingAs($admin)
        ->post(route('admin.review-opnams.items.store', $opnam), array_merge(
            hazelRevisiPayload($tarif),
            ['material_spec_id' => 999999],
        ))
        ->assertSessionHasErrors('material_spec_id');

    $this->actingAs($admin)
        ->put(route('admin.review-opnams.items.update', [$opnam, $item]), hazelRevisiPayload($tarif, 12))
        ->assertRedirect();

    $item->refresh();
    expect((float) $item->unit_price)->toBe(100000.0)
        ->and((float) $item->subtotal)->toBe(1200000.0)
        ->and((float) $opnam->fresh()->total_biaya)->toBe(1200000.0);
});

test('Admin dapat menghapus item dan total opnam dihitung ulang', function () {
    $admin = hazelRevisiBuatUser('admin', 'admin-revisi-hapus');
    $opnam = hazelRevisiBuatOpnam($admin, 'OPN-REV-ADM-003', Opnam::STATUS_MENUNGGU_REVIEW);
    $tarif = hazelRevisiBuatTarif($admin);

    $this->actingAs($admin)
        ->post(route('admin.review-opnams.items.store', $opnam), hazelRevisiPayload($tarif))
        ->assertRedirect();

    $item = $opnam->items()->firstOrFail();

    $this->actingAs($admin)
        ->delete(route('admin.review-opnams.items.destroy', [$opnam, $item]))
        ->assertRedirect();

    expect($opnam->items()->count())->toBe(0)
        ->and((float) $opnam->fresh()->total_biaya)->toBe(0.0);

    expect(ActivityLog::where('event', 'AD-07 Revisi Data Opnam')
        ->where('old_values', 'like', '%Pekerjaan revisi%')
        ->exists())->toBeTrue();
});

test('Direksi merevisi opnam saat menunggu approval dan tagihan tetap selaras', function () {
    $direksi = hazelRevisiBuatUser('direksi', 'direksi-revisi-opnam');
    $admin = hazelRevisiBuatUser('admin', 'admin-revisi-opnam');
    $opnam = hazelRevisiBuatOpnam($admin, 'OPN-REV-DIR-001', Opnam::STATUS_MENUNGGU_APPROVAL_DIREKSI);
    $billing = hazelRevisiBuatTagihan($opnam);
    $tarif = hazelRevisiBuatTarif($admin);

    $this->actingAs($admin)
        ->get(route('direksi.tagihans.revisi-opnam', $billing))
        ->assertForbidden();

    $this->actingAs($direksi)
        ->get(route('direksi.tagihans.revisi-opnam', $billing))
        ->assertOk()
        ->assertSee('Revisi '.$opnam->opnam_number);

    $this->actingAs($direksi)
        ->post(route('direksi.tagihans.revisi-opnam.items.store', $billing), hazelRevisiPayload($tarif))
        ->assertRedirect();

    $billing->refresh();
    expect((float) $billing->subtotal_amount)->toBe(1000000.0)
        ->and((float) $billing->total_amount)->toBe(1000000.0);

    $this->actingAs($direksi)
        ->post(route('direksi.tagihans.apply-adjustment', $billing), [
            'adjustment_type' => BillingAdjustment::TYPE_DISKON,
            'adjustment_value' => 100000,
            'disposition_note' => 'Diskon sesuai kesepakatan.',
        ])
        ->assertRedirect();

    $item = $opnam->items()->firstOrFail();
    $this->actingAs($direksi)
        ->put(route('direksi.tagihans.revisi-opnam.items.update', [$billing, $item]), hazelRevisiPayload($tarif, 12))
        ->assertRedirect();

    $billing->refresh();
    expect((float) $billing->subtotal_amount)->toBe(1200000.0)
        ->and((float) $billing->total_amount)->toBe(1100000.0)
        ->and((float) $billing->adjustment_amount)->toBe(100000.0);

    expect(ActivityLog::where('event', 'DR-03 Revisi Data Opnam')
        ->where('subject_id', $opnam->id)->count())->toBe(2);

    $billing->update(['status' => Billing::STATUS_DISETUJUI]);
    $this->actingAs($direksi)
        ->get(route('direksi.tagihans.revisi-opnam', $billing))
        ->assertStatus(422);
});

test('revisi oleh pihak yang salah dan item opnam lain ditolak', function () {
    $admin = hazelRevisiBuatUser('admin', 'admin-revisi-gagal');
    $direksi = hazelRevisiBuatUser('direksi', 'direksi-revisi-gagal');
    $opnam = hazelRevisiBuatOpnam($admin, 'OPN-REV-GAG-001', Opnam::STATUS_MENUNGGU_REVIEW);
    $tarif = hazelRevisiBuatTarif($admin);

    $this->actingAs($admin)
        ->post(route('admin.review-opnams.items.store', $opnam), hazelRevisiPayload($tarif))
        ->assertRedirect();
    $item = $opnam->items()->firstOrFail();

    $this->actingAs($direksi)
        ->put(route('admin.review-opnams.update-revisi', $opnam), [
            'tanggal_opnam' => '2026-11-22',
        ])
        ->assertForbidden();

    $opnamLain = hazelRevisiBuatOpnam($admin, 'OPN-REV-GAG-002', Opnam::STATUS_MENUNGGU_REVIEW);
    $this->actingAs($admin)
        ->put(route('admin.review-opnams.items.update', [$opnamLain, $item]), hazelRevisiPayload($tarif))
        ->assertNotFound();
});
