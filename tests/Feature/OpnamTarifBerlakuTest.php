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

function siapkanTarifBerlaku(): array
{
    $admin = User::create([
        'name' => 'Admin', 'username' => 'admin-tarif', 'email' => 'admin-tarif@example.com',
        'password' => 'password', 'role' => 'admin', 'is_active' => true,
    ]);
    $petugas = User::create([
        'name' => 'Petugas', 'username' => 'petugas-tarif', 'email' => 'petugas-tarif@example.com',
        'password' => 'password', 'role' => 'petugas', 'is_active' => true,
    ]);
    $owner = Owner::create(['name' => 'PT Tarif Berlaku']);
    $ship = Ship::create([
        'owner_id' => $owner->id, 'name' => 'KM Uji Tarif', 'hull_number' => 'TRF-001',
    ]);
    $category = WorkCategory::create(['name' => 'Kategori Tarif Berlaku']);
    $type = WorkType::create(['work_category_id' => $category->id, 'name' => 'Jenis Tarif Berlaku']);
    $material = MaterialSpec::create(['work_type_id' => $type->id, 'name' => 'Material Tarif Berlaku']);
    $unit = Unit::create(['code' => 'M2-TARIF', 'name' => 'm²']);

    return compact('admin', 'petugas', 'ship', 'category', 'type', 'material', 'unit');
}

function itemTarifBerlaku(array $data): array
{
    return [[
        'uid' => 'item-a',
        'work_category_id' => $data['category']->id,
        'work_type_id' => $data['type']->id,
        'material_spec_id' => $data['material']->id,
        'unit_id' => $data['unit']->id,
        'volume' => 2,
        'kompleksitas' => 'low',
        'description' => 'Pekerjaan uji tarif',
    ]];
}

function isiLangkahSatuTarifBerlaku(array $data): void
{
    test()->post(route('petugas.opnams.store-step'), [
        'step' => 1,
        'ship_id' => $data['ship']->id,
        'tanggal_opnam' => '2026-06-25',
        'lokasi' => 'Dock 01 — Graving Dock',
    ])->assertRedirect();
}

test('item memakai tarif berlaku paling baru yang tidak melewati tanggal opnam', function () {
    $data = siapkanTarifBerlaku();

    Tariff::create([
        'material_spec_id' => $data['material']->id, 'unit_id' => $data['unit']->id,
        'created_by' => $data['admin']->id, 'price' => 1000, 'effective_from' => '2026-01-01',
    ]);
    Tariff::create([
        'material_spec_id' => $data['material']->id, 'unit_id' => $data['unit']->id,
        'created_by' => $data['admin']->id, 'price' => 2000, 'effective_from' => '2026-07-01',
    ]);

    $this->actingAs($data['petugas']);
    isiLangkahSatuTarifBerlaku($data);

    $this->post(route('petugas.opnams.store-step'), [
        'step' => 2,
        'items' => itemTarifBerlaku($data),
    ])->assertRedirect(route('petugas.opnams.create', ['step' => 3]));

    $item = session('opnam_draft.items.0');
    expect((float) $item['unit_price'])->toBe(1000.0)
        ->and((float) $item['subtotal'])->toBe(2000.0)
        ->and((float) $item['faktor_ketebalan'])->toBe(1.0)
        ->and((float) $item['faktor_kompleksitas'])->toBe(1.0);
});

test('item ditolak bila belum ada tarif berlaku pada tanggal opnam', function () {
    $data = siapkanTarifBerlaku();

    Tariff::create([
        'material_spec_id' => $data['material']->id, 'unit_id' => $data['unit']->id,
        'created_by' => $data['admin']->id, 'price' => 2000, 'effective_from' => '2026-07-01',
    ]);

    $this->actingAs($data['petugas']);
    isiLangkahSatuTarifBerlaku($data);

    $this->post(route('petugas.opnams.store-step'), [
        'step' => 2,
        'items' => itemTarifBerlaku($data),
    ])->assertSessionHasErrors('items');
});

test('perubahan tarif master tidak mengubah unit price dan subtotal yang sudah tersimpan', function () {
    $data = siapkanTarifBerlaku();

    $tariff = Tariff::create([
        'material_spec_id' => $data['material']->id, 'unit_id' => $data['unit']->id,
        'created_by' => $data['admin']->id, 'price' => 1000, 'effective_from' => '2026-01-01',
    ]);

    $this->actingAs($data['petugas']);
    isiLangkahSatuTarifBerlaku($data);
    $this->post(route('petugas.opnams.store-step'), ['step' => 2, 'items' => itemTarifBerlaku($data)]);
    $this->post(route('petugas.opnams.store-step'), ['step' => 3]);
    $this->post(route('petugas.opnams.store-step'), ['step' => 4]);
    $this->post(route('petugas.opnams.store-step'), ['step' => 5]);

    $tariff->update(['price' => 5000]);

    $item = OpnamItem::firstOrFail();
    $opnam = Opnam::firstOrFail();

    expect((float) $item->unit_price)->toBe(1000.0)
        ->and((float) $item->subtotal)->toBe(2000.0)
        ->and((float) $opnam->total_biaya)->toBe(2000.0);
});
