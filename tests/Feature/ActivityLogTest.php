<?php

use App\Models\ActivityLog;
use App\Models\MaterialSpec;
use App\Models\Owner;
use App\Models\Tariff;
use App\Models\Unit;
use App\Models\User;
use App\Models\WorkCategory;
use App\Models\WorkType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function buatAdminLog(): User
{
    return User::create([
        'name' => 'Admin Log',
        'username' => 'admin-log',
        'email' => 'admin-log@example.com',
        'password' => 'password',
        'role' => User::ROLE_ADMIN,
        'is_active' => true,
    ]);
}

function buatPetugasLog(): User
{
    return User::create([
        'name' => 'Petugas Log',
        'username' => 'petugas-log',
        'email' => 'petugas-log@example.com',
        'password' => 'password',
        'role' => User::ROLE_PETUGAS,
        'is_active' => true,
    ]);
}

test('perubahan data pemilik tercatat pada riwayat aktivitas', function () {
    $admin = buatAdminLog();

    $this->actingAs($admin)
        ->post(route('admin.owners.store'), [
            'name' => 'PT Audit Marine',
            'address' => 'Semarang',
        ])
        ->assertRedirect(route('admin.owners.index'));

    $log = ActivityLog::query()
        ->where('event', 'created')
        ->where('subject_type', Owner::class)
        ->latest('id')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($admin->id)
        ->and($log->description)->toContain('PT Audit Marine')
        ->and($log->new_values['name'])->toBe('PT Audit Marine');
});

test('perubahan dan penghapusan data tercatat dengan nilai lama dan baru', function () {
    $admin = buatAdminLog();
    $owner = Owner::create(['name' => 'Nama Awal']);

    $this->actingAs($admin)
        ->put(route('admin.owners.update', $owner), [
            'name' => 'Nama Akhir',
            'address' => 'Alamat Baru',
        ])
        ->assertRedirect(route('admin.owners.index'));

    $log = ActivityLog::query()
        ->where('event', 'updated')
        ->where('subject_type', Owner::class)
        ->where('subject_id', $owner->id)
        ->latest('id')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->old_values['name'])->toBe('Nama Awal')
        ->and($log->new_values['name'])->toBe('Nama Akhir');

    $this->actingAs($admin)
        ->delete(route('admin.owners.destroy', $owner))
        ->assertRedirect(route('admin.owners.index'));

    $this->assertDatabaseHas('activity_logs', [
        'event' => 'deleted',
        'subject_type' => Owner::class,
        'subject_id' => $owner->id,
        'user_id' => $admin->id,
    ]);
});

test('petugas hanya melihat aktivitas miliknya sendiri', function () {
    $admin = buatAdminLog();
    $petugas = buatPetugasLog();

    ActivityLog::create([
        'user_id' => $admin->id,
        'event' => 'created',
        'subject_type' => Owner::class,
        'subject_id' => 1,
        'description' => 'Aktivitas milik admin',
    ]);

    ActivityLog::create([
        'user_id' => $petugas->id,
        'event' => 'updated',
        'subject_type' => Owner::class,
        'subject_id' => 2,
        'description' => 'Aktivitas milik petugas',
    ]);

    $this->actingAs($petugas)
        ->get(route('activity-logs.index'))
        ->assertOk()
        ->assertSee('Aktivitas milik petugas')
        ->assertDontSee('Aktivitas milik admin');
});

test('admin melihat seluruh aktivitas dan dapat memfilternya', function () {
    $admin = buatAdminLog();
    $petugas = buatPetugasLog();

    ActivityLog::create([
        'user_id' => $admin->id,
        'event' => 'created',
        'subject_type' => Owner::class,
        'subject_id' => 1,
        'description' => 'Pemilik dibuat oleh admin',
    ]);

    ActivityLog::create([
        'user_id' => $petugas->id,
        'event' => 'updated',
        'subject_type' => Owner::class,
        'subject_id' => 2,
        'description' => 'Pemilik diperbarui oleh petugas',
        'old_values' => ['name' => 'Nama Lama'],
        'new_values' => ['name' => 'Nama Baru'],
    ]);

    $this->actingAs($admin)
        ->get(route('activity-logs.index'))
        ->assertOk()
        ->assertSee('Pemilik dibuat oleh admin')
        ->assertSee('Pemilik diperbarui oleh petugas')
        ->assertSee('Lihat detail');

    $this->actingAs($admin)
        ->get(route('activity-logs.index', ['event' => 'updated']))
        ->assertOk()
        ->assertSee('Pemilik diperbarui oleh petugas')
        ->assertDontSee('Pemilik dibuat oleh admin');

    $this->actingAs($admin)
        ->get(route('activity-logs.index', ['user_id' => $petugas->id]))
        ->assertOk()
        ->assertSee('Pemilik diperbarui oleh petugas')
        ->assertDontSee('Pemilik dibuat oleh admin');
});

test('perubahan harga tarif tercatat pada audit log', function () {
    $admin = buatAdminLog();

    $category = WorkCategory::create(['name' => 'Pengecatan']);
    $type = WorkType::create(['work_category_id' => $category->id, 'name' => 'Cat Dasar']);
    $spec = MaterialSpec::create(['work_type_id' => $type->id, 'name' => 'Epoxy']);
    $unit = Unit::create(['code' => 'M2', 'name' => 'Meter Persegi']);

    $tariff = Tariff::create([
        'material_spec_id' => $spec->id,
        'unit_id' => $unit->id,
        'created_by' => $admin->id,
        'price' => 100000,
        'effective_from' => '2026-01-01',
    ]);

    $tariff->update(['price' => 125000]);

    $log = ActivityLog::query()
        ->where('event', 'updated')
        ->where('subject_type', Tariff::class)
        ->where('subject_id', $tariff->id)
        ->latest('id')
        ->first();

    expect($log)->not->toBeNull()
        ->and((float) $log->old_values['price'])->toBe(100000.0)
        ->and((float) $log->new_values['price'])->toBe(125000.0);
});

test('tamu tidak boleh mengakses riwayat aktivitas', function () {
    $this->get(route('activity-logs.index'))
        ->assertRedirect(route('login'));
});
