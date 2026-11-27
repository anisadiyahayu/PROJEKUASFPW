<?php

use App\Models\ActivityLog;
use App\Models\Billing;
use App\Models\BillingAdjustment;
use App\Models\Invoice;
use App\Models\Opnam;
use App\Models\Owner;
use App\Models\Ship;
use App\Models\StatusHistory;
use App\Models\TechnicalReport;
use App\Models\User;
use App\Services\DokumenTagihanService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function hazelDokumenBuatUser(string $role, string $username): User
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

function hazelDokumenBuatTagihan(User $creator, string $number, bool $disetujui = false): Billing
{
    $owner = Owner::create(['name' => 'Owner '.$number]);
    $ship = Ship::create([
        'owner_id' => $owner->id,
        'name' => 'Kapal '.$number,
        'hull_number' => 'HL-'.$number,
    ]);
    $opnam = Opnam::create([
        'opnam_number' => $number,
        'ship_id' => $ship->id,
        'created_by' => $creator->id,
        'status' => $disetujui
            ? Opnam::STATUS_DISETUJUI
            : Opnam::STATUS_MENUNGGU_APPROVAL_DIREKSI,
        'tanggal_opnam' => '2026-11-20',
        'total_biaya' => 1250000,
    ]);

    $billing = Billing::create([
        'opnam_id' => $opnam->id,
        'status' => $disetujui ? Billing::STATUS_DISETUJUI : Billing::STATUS_MENUNGGU_APPROVAL,
        'subtotal_amount' => 1250000,
        'adjustment_amount' => 0,
        'total_amount' => 1250000,
    ]);
    $billing->update(['billing_number' => sprintf('BLG-%06d', $billing->id)]);

    return $billing;
}

test('Direksi mencatat penyesuaian dan catatan disposisi wajib', function () {
    $direksi = hazelDokumenBuatUser('direksi', 'direksi-adjustment');
    $admin = hazelDokumenBuatUser('admin', 'admin-adjustment');
    $billing = hazelDokumenBuatTagihan($admin, 'OPN-ADJ-001');

    $this->actingAs($direksi)
        ->post(route('direksi.tagihans.apply-adjustment', $billing), [
            'adjustment_type' => 'diskon',
            'adjustment_value' => 100000,
        ])
        ->assertSessionHasErrors('disposition_note');

    $this->actingAs($direksi)
        ->post(route('direksi.tagihans.apply-adjustment', $billing), [
            'adjustment_type' => 'diskon',
            'adjustment_value' => 100000,
            'disposition_note' => 'Diskon sesuai kesepakatan pemilik.',
        ])
        ->assertRedirect();

    $billing->refresh();
    expect((float) $billing->adjustment_amount)->toBe(100000.0)
        ->and((float) $billing->total_amount)->toBe(1150000.0)
        ->and(BillingAdjustment::where('billing_id', $billing->id)->count())->toBe(1);

    $this->actingAs($direksi)
        ->post(route('direksi.tagihans.apply-adjustment', $billing), [
            'adjustment_type' => 'markup',
            'adjustment_value' => 100000,
            'disposition_note' => 'Markup untuk pekerjaan tambahan.',
        ])
        ->assertRedirect();

    $billing->refresh();
    expect((float) $billing->total_amount)->toBe(1350000.0)
        ->and(BillingAdjustment::where('billing_id', $billing->id)->count())->toBe(1)
        ->and($billing->adjustments->first()->adjustment_type)->toBe('markup');

    expect(ActivityLog::where('event', 'DR-04 Diskon atau Markup')->exists())->toBeTrue();
});

test('Direksi menyetujui tagihan dan sistem membuat draf invoice serta technical report', function () {
    $direksi = hazelDokumenBuatUser('direksi', 'direksi-approve');
    $admin = hazelDokumenBuatUser('admin', 'admin-approve');
    $billing = hazelDokumenBuatTagihan($admin, 'OPN-APR-001');

    $this->actingAs($direksi)
        ->post(route('direksi.tagihans.approve', $billing))
        ->assertRedirect();

    $billing->refresh();
    expect($billing->status)->toBe(Billing::STATUS_DISETUJUI)
        ->and($billing->opnam->status)->toBe(Opnam::STATUS_DISETUJUI)
        ->and($billing->decided_by)->toBe($direksi->id);

    $invoice = Invoice::where('billing_id', $billing->id)->firstOrFail();
    $report = TechnicalReport::where('billing_id', $billing->id)->firstOrFail();

    expect($invoice->status)->toBe(Invoice::STATUS_DRAFT)
        ->and($invoice->invoice_number)->toStartWith('INV-')
        ->and($invoice->issued_by)->toBe($direksi->id)
        ->and((float) $invoice->total_amount)->toBe(1250000.0)
        ->and($invoice->ship_name)->toBe('Kapal OPN-APR-001')
        ->and($report->report_number)->toStartWith('TR-')
        ->and($report->issued_by)->toBe($direksi->id);

    expect(StatusHistory::where('statusable_type', $billing->getMorphClass())
        ->where('to_status', Billing::STATUS_DISETUJUI)
        ->exists())->toBeTrue();
});

test('Direksi menolak tagihan dan alasan tersimpan di riwayat status', function () {
    $direksi = hazelDokumenBuatUser('direksi', 'direksi-reject');
    $admin = hazelDokumenBuatUser('admin', 'admin-reject');
    $billing = hazelDokumenBuatTagihan($admin, 'OPN-RJT-001');

    $this->actingAs($direksi)
        ->post(route('direksi.tagihans.reject', $billing), [])
        ->assertSessionHasErrors('alasan');

    $this->actingAs($direksi)
        ->post(route('direksi.tagihans.reject', $billing), [
            'alasan' => 'Spesifikasi pekerjaan belum lengkap.',
        ])
        ->assertRedirect();

    $billing->refresh();
    expect($billing->status)->toBe(Billing::STATUS_DITOLAK)
        ->and($billing->opnam->status)->toBe(Opnam::STATUS_DITOLAK)
        ->and(Invoice::where('billing_id', $billing->id)->exists())->toBeFalse();

    expect(StatusHistory::where('statusable_type', $billing->getMorphClass())
        ->where('to_status', Billing::STATUS_DITOLAK)
        ->firstOrFail()->note)->toBe('Spesifikasi pekerjaan belum lengkap.');

    $this->actingAs($direksi)
        ->post(route('direksi.tagihans.reject', $billing), [
            'alasan' => 'Pengajuan ulang.',
        ])
        ->assertStatus(422);
});

test('Admin dan Direksi dapat merevisi invoice draft tanpa mengubah nominal', function () {
    $direksi = hazelDokumenBuatUser('direksi', 'direksi-revisi');
    $admin = hazelDokumenBuatUser('admin', 'admin-revisi');
    $billing = hazelDokumenBuatTagihan($admin, 'OPN-REV-001', true);
    app(DokumenTagihanService::class)->terbitkanDraf($billing, $direksi);

    $invoice = Invoice::where('billing_id', $billing->id)->firstOrFail();

    $this->actingAs($admin)
        ->put(route('admin.invoices.update', $invoice), [
            'ship_name' => 'Kapal Revisi Admin',
            'hull_number' => $invoice->hull_number,
            'owner_name' => $invoice->owner_name,
            'owner_address' => $invoice->owner_address,
            'issue_date' => '2026-11-21',
        ])
        ->assertRedirect();

    $invoice->refresh();
    expect($invoice->ship_name)->toBe('Kapal Revisi Admin')
        ->and($invoice->revision_count)->toBe(1)
        ->and((float) $invoice->total_amount)->toBe(1250000.0);

    $this->actingAs($direksi)
        ->put(route('direksi.dokumen.update-invoice', $invoice), [
            'ship_name' => 'Kapal Revisi Direksi',
            'hull_number' => $invoice->hull_number,
            'owner_name' => $invoice->owner_name,
            'owner_address' => $invoice->owner_address,
            'issue_date' => '2026-11-22',
        ])
        ->assertRedirect();

    $invoice->refresh();
    expect($invoice->ship_name)->toBe('Kapal Revisi Direksi')
        ->and($invoice->revision_count)->toBe(2);

    expect(ActivityLog::where('event', 'Revisi Invoice')->count())->toBe(2);

    $invoice->update(['status' => Invoice::STATUS_TERBIT]);

    $this->actingAs($direksi)
        ->put(route('direksi.dokumen.update-invoice', $invoice), [
            'ship_name' => 'Kapal Terbit',
            'hull_number' => $invoice->hull_number,
            'owner_name' => $invoice->owner_name,
            'owner_address' => $invoice->owner_address,
            'issue_date' => '2026-11-23',
        ])
        ->assertStatus(422);
});

test('Ekspor pertama menerbitkan invoice dan mengubah opnam menjadi dokumen terbit', function () {
    $direksi = hazelDokumenBuatUser('direksi', 'direksi-publikasi');
    $admin = hazelDokumenBuatUser('admin', 'admin-publikasi');
    $billing = hazelDokumenBuatTagihan($admin, 'OPN-PUB-001', true);
    app(DokumenTagihanService::class)->terbitkanDraf($billing, $direksi);

    $invoice = Invoice::where('billing_id', $billing->id)->firstOrFail();
    $response = $this->actingAs($direksi)
        ->get(route('direksi.dokumen.download-invoice', $invoice));

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('application/pdf');

    $invoice->refresh();
    $billing->refresh();
    expect($invoice->status)->toBe(Invoice::STATUS_TERBIT)
        ->and($billing->opnam->status)->toBe(Opnam::STATUS_DOKUMEN_TERBIT);

    expect(StatusHistory::where('statusable_type', $billing->opnam->getMorphClass())
        ->where('to_status', Opnam::STATUS_DOKUMEN_TERBIT)
        ->exists())->toBeTrue();

    $this->actingAs($direksi)
        ->get(route('direksi.dokumen.edit-invoice', $invoice))
        ->assertStatus(422);

    $this->actingAs($direksi)
        ->get(route('direksi.dokumen.download-invoice', $invoice))
        ->assertOk();
});

test('halaman dokumen dan form revisi invoice dapat dibuka', function () {
    $direksi = hazelDokumenBuatUser('direksi', 'direksi-view');
    $admin = hazelDokumenBuatUser('admin', 'admin-view');
    $billing = hazelDokumenBuatTagihan($admin, 'OPN-VIW-001', true);
    app(DokumenTagihanService::class)->terbitkanDraf($billing, $direksi);

    $invoice = Invoice::where('billing_id', $billing->id)->firstOrFail();

    $this->actingAs($direksi)
        ->get(route('direksi.dokumen.index'))
        ->assertOk()
        ->assertSee($invoice->invoice_number)
        ->assertSee('Technical Report');

    $this->actingAs($direksi)
        ->get(route('direksi.dokumen.edit-invoice', $invoice))
        ->assertOk()
        ->assertSee('Simpan Revisi');

    $this->actingAs($admin)
        ->get(route('admin.invoices.index'))
        ->assertOk()
        ->assertSee($invoice->invoice_number);

    $this->actingAs($admin)
        ->get(route('admin.invoices.edit', $invoice))
        ->assertOk()
        ->assertSee('Nominal');
});

test('Admin dapat mengekspor invoice draft ke PDF', function () {
    $direksi = hazelDokumenBuatUser('direksi', 'direksi-ekspor');
    $admin = hazelDokumenBuatUser('admin', 'admin-ekspor');
    $billing = hazelDokumenBuatTagihan($admin, 'OPN-EKS-001', true);
    app(DokumenTagihanService::class)->terbitkanDraf($billing, $direksi);

    $invoice = Invoice::where('billing_id', $billing->id)->firstOrFail();
    $response = $this->actingAs($admin)
        ->get(route('admin.invoices.download', $invoice));

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('application/pdf')
        ->and($invoice->refresh()->status)->toBe(Invoice::STATUS_TERBIT);
});
