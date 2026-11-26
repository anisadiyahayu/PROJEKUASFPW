<?php

use App\Models\MaterialSpec;
use App\Models\Opnam;
use App\Models\OpnamAttachment;
use App\Models\OpnamItem;
use App\Models\Owner;
use App\Models\Ship;
use App\Models\Tariff;
use App\Models\Unit;
use App\Models\User;
use App\Models\WorkCategory;
use App\Models\WorkType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function siapkanDraftUntukEdit(): array
{
    $admin = User::create([
        'name' => 'Admin', 'username' => 'admin-edit', 'email' => 'admin-edit@example.com',
        'password' => 'password', 'role' => 'admin', 'is_active' => true,
    ]);
    $petugas = User::create([
        'name' => 'Petugas', 'username' => 'petugas-edit', 'email' => 'petugas-edit@example.com',
        'password' => 'password', 'role' => 'petugas', 'is_active' => true,
    ]);
    $petugasLain = User::create([
        'name' => 'Petugas Lain', 'username' => 'petugas-lain', 'email' => 'petugas-lain@example.com',
        'password' => 'password', 'role' => 'petugas', 'is_active' => true,
    ]);
    $owner = Owner::create(['name' => 'Owner Edit']);
    $ship = Ship::create(['owner_id' => $owner->id, 'name' => 'KM Edit', 'hull_number' => 'EDT-001']);
    $category = WorkCategory::create(['name' => 'Kategori Edit']);
    $type = WorkType::create(['work_category_id' => $category->id, 'name' => 'Jenis Edit']);
    $material = MaterialSpec::create(['work_type_id' => $type->id, 'name' => 'Material Edit']);
    $materialDua = MaterialSpec::create(['work_type_id' => $type->id, 'name' => 'Material Dua']);
    $unit = Unit::create(['code' => 'M2-EDIT', 'name' => 'm²']);
    $unitDua = Unit::create(['code' => 'L-EDIT', 'name' => 'm']);
    $tariff = Tariff::create([
        'material_spec_id' => $material->id, 'unit_id' => $unit->id, 'created_by' => $admin->id,
        'price' => 1000, 'effective_from' => '2026-01-01',
    ]);
    $tariffDua = Tariff::create([
        'material_spec_id' => $materialDua->id, 'unit_id' => $unitDua->id, 'created_by' => $admin->id,
        'price' => 2500, 'effective_from' => '2026-01-01',
    ]);

    $opnam = Opnam::create([
        'opnam_number' => 'OPN-EDIT-001', 'ship_id' => $ship->id, 'created_by' => $petugas->id,
        'status' => Opnam::STATUS_DRAFT, 'tanggal_opnam' => '2026-06-25', 'total_biaya' => 2000,
    ]);
    $item = OpnamItem::create([
        'opnam_id' => $opnam->id, 'tariff_id' => $tariff->id, 'volume' => 2,
        'unit_price' => 1000, 'subtotal' => 2000, 'faktor' => 1,
        'kompleksitas' => 'low', 'description' => 'Item awal', 'sort_order' => 1,
    ]);

    return compact('admin', 'petugas', 'petugasLain', 'ship', 'category', 'type', 'material',
        'materialDua', 'unit', 'unitDua', 'tariff', 'tariffDua', 'opnam', 'item');
}

function dataItemEdit(array $data, array $overrides = []): array
{
    return array_merge([
        'work_category_id' => $data['category']->id,
        'work_type_id' => $data['type']->id,
        'material_spec_id' => $data['material']->id,
        'unit_id' => $data['unit']->id,
        'volume' => 2,
        'kompleksitas' => 'low',
        'description' => 'Item awal',
    ], $overrides);
}

test('pemilik draft dapat mengubah volume dan total biaya dihitung ulang', function () {
    $data = siapkanDraftUntukEdit();
    $this->actingAs($data['petugas']);

    $this->put(route('petugas.opnams.items.update', [$data['opnam'], $data['item']]), dataItemEdit($data, ['volume' => 3]))
        ->assertRedirect(route('petugas.opnams.edit', $data['opnam']));

    $item = $data['item']->fresh();
    expect((float) $item->volume)->toBe(3.0)
        ->and((float) $item->unit_price)->toBe(1000.0)
        ->and((float) $item->subtotal)->toBe(3000.0)
        ->and((float) $data['opnam']->fresh()->total_biaya)->toBe(3000.0);
});

test('mengubah material dan satuan mencari ulang tarif berlaku', function () {
    $data = siapkanDraftUntukEdit();
    $this->actingAs($data['petugas']);

    $this->put(route('petugas.opnams.items.update', [$data['opnam'], $data['item']]), dataItemEdit($data, [
        'material_spec_id' => $data['materialDua']->id,
        'unit_id' => $data['unitDua']->id,
        'volume' => 1,
    ]))->assertRedirect();

    $item = $data['item']->fresh();
    expect($item->tariff_id)->toBe($data['tariffDua']->id)
        ->and((float) $item->unit_price)->toBe(2500.0)
        ->and((float) $item->subtotal)->toBe(2500.0)
        ->and((float) $data['opnam']->fresh()->total_biaya)->toBe(2500.0);
});

test('menambah tarif baru di master tidak mengubah unit_price item lama saat volume diubah', function () {
    $data = siapkanDraftUntukEdit();
    Tariff::create([
        'material_spec_id' => $data['material']->id, 'unit_id' => $data['unit']->id,
        'created_by' => $data['admin']->id, 'price' => 5000, 'effective_from' => '2026-06-01',
    ]);
    $this->actingAs($data['petugas']);

    $this->put(route('petugas.opnams.items.update', [$data['opnam'], $data['item']]), dataItemEdit($data, ['volume' => 4]))
        ->assertRedirect();

    $item = $data['item']->fresh();
    expect((float) $item->unit_price)->toBe(1000.0)
        ->and((float) $item->subtotal)->toBe(4000.0);
});

test('item hanya dapat diubah pada draft milik sendiri', function () {
    $data = siapkanDraftUntukEdit();

    $this->actingAs($data['petugasLain'])
        ->put(route('petugas.opnams.items.update', [$data['opnam'], $data['item']]), dataItemEdit($data))
        ->assertForbidden();

    $data['opnam']->update(['status' => Opnam::STATUS_MENUNGGU_REVIEW]);
    $this->actingAs($data['petugas'])
        ->put(route('petugas.opnams.items.update', [$data['opnam'], $data['item']]), dataItemEdit($data))
        ->assertForbidden();
});

test('item dapat ditambahkan dan dihapus pada draft', function () {
    Storage::fake('public');
    $data = siapkanDraftUntukEdit();
    OpnamAttachment::create([
        'opnam_item_id' => $data['item']->id, 'uploaded_by' => $data['petugas']->id,
        'file_path' => 'opnam-attachments/lama.jpg', 'original_name' => 'lama.jpg',
        'mime_type' => 'image/jpeg', 'file_size' => 100,
    ]);
    Storage::disk('public')->put('opnam-attachments/lama.jpg', 'isi');

    $this->actingAs($data['petugas']);

    $this->post(route('petugas.opnams.items.store', $data['opnam']), dataItemEdit($data, [
        'volume' => 1, 'description' => 'Item tambahan',
    ]))->assertRedirect();

    expect($data['opnam']->items()->count())->toBe(2)
        ->and((float) $data['opnam']->fresh()->total_biaya)->toBe(3000.0);

    $this->delete(route('petugas.opnams.items.destroy', [$data['opnam'], $data['item']]))
        ->assertRedirect();

    expect($data['opnam']->items()->count())->toBe(1)
        ->and(OpnamAttachment::count())->toBe(0)
        ->and((float) $data['opnam']->fresh()->total_biaya)->toBe(1000.0);
    Storage::disk('public')->assertMissing('opnam-attachments/lama.jpg');
});

test('item lama tanpa nilai faktor mendapat default 1 dan subtotal tidak berubah', function () {
    $data = siapkanDraftUntukEdit();
    $item = $data['item']->fresh();

    expect((float) $item->faktor_ketebalan)->toBe(1.0)
        ->and((float) $item->faktor_kompleksitas)->toBe(1.0)
        ->and((float) $item->subtotal)->toBe(2000.0);
});

test('faktor hasil edit tersimpan terpisah sebagai snapshot', function () {
    config()->set('opnam.faktor_ketebalan.10', 1.2);
    config()->set('opnam.faktor_kompleksitas.high', 1.1);

    $data = siapkanDraftUntukEdit();
    $this->actingAs($data['petugas']);

    $this->put(route('petugas.opnams.items.update', [$data['opnam'], $data['item']]), dataItemEdit($data, [
        'volume' => 3, 'ketebalan' => 10, 'kompleksitas' => 'high',
    ]))->assertRedirect();

    $item = $data['item']->fresh();
    expect((float) $item->faktor_ketebalan)->toBe(1.2)
        ->and((float) $item->faktor_kompleksitas)->toBe(1.1)
        ->and((float) $item->faktor)->toBe(1.32)
        ->and((float) $item->subtotal)->toBe(3960.0)
        ->and((float) $data['opnam']->fresh()->total_biaya)->toBe(3960.0);
});

test('halaman edit menampilkan item draft milik sendiri', function () {
    $data = siapkanDraftUntukEdit();

    $this->actingAs($data['petugas'])
        ->get(route('petugas.opnams.edit', $data['opnam']))
        ->assertOk()
        ->assertSee('Item awal')
        ->assertSee('Tambah Item Pekerjaan');

    $this->actingAs($data['petugasLain'])
        ->get(route('petugas.opnams.edit', $data['opnam']))
        ->assertForbidden();
});

test('admin dan direksi tidak dapat mengakses edit item draft petugas', function () {
    $data = siapkanDraftUntukEdit();
    $direksi = User::create([
        'name' => 'Direksi', 'username' => 'direksi-edit', 'email' => 'direksi-edit@example.com',
        'password' => 'password', 'role' => 'direksi', 'is_active' => true,
    ]);

    foreach ([$data['admin'], $direksi] as $user) {
        $this->actingAs($user)
            ->get(route('petugas.opnams.edit', $data['opnam']))
            ->assertForbidden();
        $this->actingAs($user)
            ->put(route('petugas.opnams.items.update', [$data['opnam'], $data['item']]), dataItemEdit($data))
            ->assertForbidden();
        $this->actingAs($user)
            ->delete(route('petugas.opnams.items.destroy', [$data['opnam'], $data['item']]))
            ->assertForbidden();
    }
});

test('item tidak dapat diubah pada status perlu_revisi', function () {
    $data = siapkanDraftUntukEdit();
    $data['opnam']->update(['status' => Opnam::STATUS_PERLU_REVISI]);

    $this->actingAs($data['petugas'])
        ->put(route('petugas.opnams.items.update', [$data['opnam'], $data['item']]), dataItemEdit($data))
        ->assertForbidden();
});
