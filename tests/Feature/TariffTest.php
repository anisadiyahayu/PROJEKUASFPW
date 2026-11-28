<?php

use App\Models\ActivityLog;
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
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

function penggunaTarif(string $role = 'admin'): User
{
    static $id = 0;
    $id++;

    return User::create([
        'name' => ucfirst($role),
        'username' => "{$role}-tarif-{$id}",
        'email' => "{$role}-tarif-{$id}@example.com",
        'password' => 'password',
        'role' => $role,
        'is_active' => true,
    ]);
}

function siapkanTarif(): array
{
    $category = WorkCategory::create(['name' => 'Kategori Tarif Uji']);
    $workType = WorkType::create([
        'work_category_id' => $category->id,
        'name' => 'Jenis Tarif Uji',
    ]);
    $material = MaterialSpec::create([
        'work_type_id' => $workType->id,
        'name' => 'Material Tarif Uji',
    ]);
    $unit = Unit::create(['code' => 'TARIF-M2', 'name' => 'Meter persegi']);
    $admin = penggunaTarif();

    return compact('category', 'workType', 'material', 'unit', 'admin');
}

function payloadTarif(array $data, array $overrides = []): array
{
    return array_merge([
        'material_spec_id' => $data['material']->id,
        'unit_id' => $data['unit']->id,
        'price' => '1250.50',
        'effective_from' => '2026-01-01',
    ], $overrides);
}

test('admin dapat melihat daftar tarif beserta relasinya dan menu master tarif', function () {
    $data = siapkanTarif();
    $tariff = Tariff::create([
        ...payloadTarif($data),
        'created_by' => $data['admin']->id,
    ]);

    $this->actingAs($data['admin'])
        ->get(route('admin.tariffs.index'))
        ->assertOk()
        ->assertSee('Master Tarif')
        ->assertSee('Material Tarif Uji')
        ->assertSee('Meter persegi')
        ->assertSee('1.250,50')
        ->assertSee('Tambah Tarif Baru')
        ->assertDontSee('Edit');

    expect($tariff->creator->is($data['admin']))->toBeTrue();
});

test('admin dapat membuka form tambah dengan material aktif dan satuan', function () {
    $data = siapkanTarif();

    $this->actingAs($data['admin'])
        ->get(route('admin.tariffs.create'))
        ->assertOk()
        ->assertSee('Material Tarif Uji')
        ->assertSee('Meter persegi')
        ->assertSee('effective_from');
});

test('admin dapat menambah record tarif baru dan mencatat pembuat serta activity log', function () {
    $data = siapkanTarif();

    $this->actingAs($data['admin'])
        ->post(route('admin.tariffs.store'), payloadTarif($data))
        ->assertRedirect(route('admin.tariffs.index'))
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status');

    $tariff = Tariff::firstOrFail();
    expect((int) $tariff->material_spec_id)->toBe($data['material']->id)
        ->and((int) $tariff->unit_id)->toBe($data['unit']->id)
        ->and((int) $tariff->created_by)->toBe($data['admin']->id)
        ->and((float) $tariff->price)->toBe(1250.5)
        ->and($tariff->effective_from->toDateString())->toBe('2026-01-01')
        ->and($tariff->created_at)->not->toBeNull();

    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $data['admin']->id,
        'subject_type' => $tariff->getMorphClass(),
        'subject_id' => $tariff->id,
        'event' => 'Tarif dibuat',
    ]);
    expect(ActivityLog::where('subject_id', $tariff->id)->first()->new_values)
        ->toMatchArray(['price' => '1250.50', 'effective_from' => '2026-01-01 00:00:00']);
});

test('kombinasi material satuan dan tanggal efektif harus unik', function () {
    $data = siapkanTarif();
    Tariff::create([...payloadTarif($data), 'created_by' => $data['admin']->id]);

    $this->actingAs($data['admin'])
        ->from(route('admin.tariffs.create'))
        ->post(route('admin.tariffs.store'), payloadTarif($data, ['price' => '2000.00']))
        ->assertRedirect(route('admin.tariffs.create'))
        ->assertSessionHasErrors('effective_from');

    $this->assertDatabaseCount('tariffs', 1);
});

test('database memiliki composite unique untuk material satuan dan tanggal efektif', function () {
    $data = siapkanTarif();
    Tariff::create([...payloadTarif($data), 'created_by' => $data['admin']->id]);

    expect(fn () => Tariff::create([
        ...payloadTarif($data, ['price' => '2000.00']),
        'created_by' => $data['admin']->id,
    ]))->toThrow(QueryException::class);
});

test('tarif baru pada tanggal berlaku lain menambah riwayat tanpa mengubah snapshot opnam lama', function () {
    $data = siapkanTarif();
    $tariffAwal = Tariff::create([
        ...payloadTarif($data, ['price' => '1000.00']),
        'created_by' => $data['admin']->id,
    ]);
    $owner = Owner::create(['name' => 'Owner Tarif']);
    $ship = Ship::create([
        'owner_id' => $owner->id,
        'name' => 'Kapal Tarif',
        'hull_number' => 'TARIF-001',
    ]);
    $opnam = Opnam::create([
        'opnam_number' => 'OP-TARIF-001',
        'ship_id' => $ship->id,
        'created_by' => penggunaTarif('petugas')->id,
        'status' => 'draft',
        'tanggal_opnam' => '2026-01-10',
        'total_biaya' => 2000,
    ]);
    $item = OpnamItem::create([
        'opnam_id' => $opnam->id,
        'tariff_id' => $tariffAwal->id,
        'volume' => 2,
        'unit_price' => 1000,
        'subtotal' => 2000,
        'sort_order' => 1,
    ]);

    $this->actingAs($data['admin'])
        ->post(route('admin.tariffs.store'), payloadTarif($data, [
            'price' => '2500.00',
            'effective_from' => '2026-02-01',
        ]))
        ->assertRedirect(route('admin.tariffs.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseCount('tariffs', 2);
    $this->assertDatabaseHas('tariffs', [
        'id' => $tariffAwal->id,
        'price' => '1000.00',
    ]);
    expect($tariffAwal->fresh()->effective_from->toDateString())->toBe('2026-01-01');
    $this->assertDatabaseHas('opnam_items', [
        'id' => $item->id,
        'tariff_id' => $tariffAwal->id,
        'unit_price' => '1000.00',
        'subtotal' => '2000.00',
    ]);

    $newLog = ActivityLog::where('event', 'Tarif dibuat')
        ->where('subject_id', '!=', $tariffAwal->id)
        ->firstOrFail();
    expect($newLog->old_values)->toMatchArray([
        'price' => '1000.00',
        'effective_from' => '2026-01-01',
    ]);
});

test('tarif tersimpan menolak perubahan melalui update, save, fill, dan forceFill', function () {
    $data = siapkanTarif();
    $tariff = Tariff::create([
        ...payloadTarif($data, ['price' => '1000.00']),
        'created_by' => $data['admin']->id,
    ]);

    expect(fn () => $tariff->update(['price' => '2000.00']))
        ->toThrow(LogicException::class);
    expect($tariff->fresh()->price)->toBe('1000.00');

    $tariff->refresh()->fill(['price' => '3000.00']);
    expect(fn () => $tariff->save())->toThrow(LogicException::class);
    expect($tariff->fresh()->price)->toBe('1000.00');

    $tariff->refresh()->forceFill(['price' => '4000.00']);
    expect(fn () => $tariff->save())->toThrow(LogicException::class);
    expect($tariff->fresh()->price)->toBe('1000.00');
});

test('tarif yang belum dipakai dapat di-hard-delete dan dicatat', function () {
    $data = siapkanTarif();
    $tariff = Tariff::create([
        ...payloadTarif($data),
        'created_by' => $data['admin']->id,
    ]);

    $this->actingAs($data['admin'])
        ->delete(route('admin.tariffs.destroy', $tariff))
        ->assertRedirect(route('admin.tariffs.index'))
        ->assertSessionHas('status');

    $this->assertDatabaseMissing('tariffs', ['id' => $tariff->id]);
    $this->assertDatabaseHas('activity_logs', [
        'subject_type' => $tariff->getMorphClass(),
        'subject_id' => $tariff->id,
        'event' => 'Tarif dihapus',
    ]);
});

test('tarif yang sudah dipakai item opnam tidak dapat dihapus', function () {
    $data = siapkanTarif();
    $tariff = Tariff::create([
        ...payloadTarif($data),
        'created_by' => $data['admin']->id,
    ]);
    $owner = Owner::create(['name' => 'Owner Tarif Terpakai']);
    $ship = Ship::create([
        'owner_id' => $owner->id,
        'name' => 'Kapal Tarif Terpakai',
        'hull_number' => 'TARIF-002',
    ]);
    $opnam = Opnam::create([
        'opnam_number' => 'OP-TARIF-002',
        'ship_id' => $ship->id,
        'created_by' => penggunaTarif('petugas')->id,
        'status' => 'draft',
        'tanggal_opnam' => '2026-01-10',
        'total_biaya' => 1000,
    ]);
    $item = OpnamItem::create([
        'opnam_id' => $opnam->id,
        'tariff_id' => $tariff->id,
        'volume' => 1,
        'unit_price' => 1000,
        'subtotal' => 1000,
        'sort_order' => 1,
    ]);

    $this->actingAs($data['admin'])
        ->delete(route('admin.tariffs.destroy', $tariff))
        ->assertRedirect(route('admin.tariffs.index'))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('tariffs', ['id' => $tariff->id]);
    $this->assertDatabaseHas('opnam_items', [
        'id' => $item->id,
        'tariff_id' => $tariff->id,
        'unit_price' => '1000.00',
        'subtotal' => '1000.00',
    ]);
    $this->assertDatabaseMissing('activity_logs', [
        'subject_type' => $tariff->getMorphClass(),
        'subject_id' => $tariff->id,
        'event' => 'Tarif dihapus',
    ]);
});

test('tarif harus merujuk material aktif satuan valid harga numerik dan tanggal berlaku valid', function () {
    $data = siapkanTarif();
    $deletedMaterial = MaterialSpec::create([
        'work_type_id' => $data['workType']->id,
        'name' => 'Material Tarif Dihapus',
    ]);
    $deletedMaterial->delete();

    $this->actingAs($data['admin'])
        ->from(route('admin.tariffs.create'))
        ->post(route('admin.tariffs.store'), [
            'material_spec_id' => $deletedMaterial->id,
            'unit_id' => 'invalid',
            'price' => 'bukan angka',
            'effective_from' => 'invalid',
        ])
        ->assertRedirect(route('admin.tariffs.create'))
        ->assertSessionHasErrors([
            'material_spec_id',
            'unit_id',
            'price',
            'effective_from',
        ]);
});

test('harga tarif harus non-negatif dan berada dalam rentang decimal 15,2', function () {
    $data = siapkanTarif();

    $this->actingAs($data['admin'])
        ->from(route('admin.tariffs.create'))
        ->post(route('admin.tariffs.store'), payloadTarif($data, ['price' => '-0.01']))
        ->assertRedirect(route('admin.tariffs.create'))
        ->assertSessionHasErrors('price');

    $this->actingAs($data['admin'])
        ->post(route('admin.tariffs.store'), payloadTarif($data, [
            'price' => '9999999999999.99',
        ]))
        ->assertRedirect(route('admin.tariffs.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseCount('tariffs', 1);
});

test('tarif hanya dapat dikelola Admin dan tidak menyediakan route edit atau update', function () {
    $data = siapkanTarif();
    $tariff = Tariff::create([
        ...payloadTarif($data),
        'created_by' => $data['admin']->id,
    ]);

    expect(Route::has('admin.tariffs.edit'))->toBeFalse()
        ->and(Route::has('admin.tariffs.update'))->toBeFalse();

    foreach ([penggunaTarif('petugas'), penggunaTarif('direksi')] as $user) {
        $this->actingAs($user)
            ->get(route('admin.tariffs.index'))
            ->assertForbidden();
        $this->actingAs($user)
            ->get(route('admin.tariffs.create'))
            ->assertForbidden();
        $this->actingAs($user)
            ->post(route('admin.tariffs.store'), payloadTarif($data))
            ->assertForbidden();
        $this->actingAs($user)
            ->delete(route('admin.tariffs.destroy', $tariff))
            ->assertForbidden();
    }
});
