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

function penggunaMaterialSpec(string $role = 'admin'): User
{
    static $id = 0;
    $id++;

    return User::create([
        'name' => ucfirst($role),
        'username' => "{$role}-material-spec-{$id}",
        'email' => "{$role}-material-spec-{$id}@example.com",
        'password' => 'password',
        'role' => $role,
        'is_active' => true,
    ]);
}

function siapkanMaterialSpec(): array
{
    $category = WorkCategory::create(['name' => 'Kategori Material Uji']);
    $workType = WorkType::create([
        'work_category_id' => $category->id,
        'name' => 'Jenis Material Uji',
    ]);
    $materialSpec = MaterialSpec::create([
        'work_type_id' => $workType->id,
        'name' => 'Pelat Uji',
    ]);

    return compact('category', 'workType', 'materialSpec');
}

function siapkanTarifMaterialSpec(MaterialSpec $materialSpec): Tariff
{
    $admin = penggunaMaterialSpec();
    $unit = Unit::create(['code' => 'MS-M2', 'name' => 'Meter persegi']);

    return Tariff::create([
        'material_spec_id' => $materialSpec->id,
        'unit_id' => $unit->id,
        'created_by' => $admin->id,
        'price' => 2500,
        'effective_from' => '2026-01-01',
    ]);
}

test('admin dapat melihat material dan jenis pekerjaannya', function () {
    $data = siapkanMaterialSpec();

    $this->actingAs(penggunaMaterialSpec())
        ->get(route('admin.material-specs.index'))
        ->assertOk()
        ->assertSee('Master Material/Spesifikasi')
        ->assertSee('Pelat Uji')
        ->assertSee('Jenis Material Uji');
});

test('daftar material tetap menampilkan jenis pekerjaan yang sudah soft delete', function () {
    $data = siapkanMaterialSpec();
    $data['workType']->delete();

    $this->actingAs(penggunaMaterialSpec())
        ->get(route('admin.material-specs.index'))
        ->assertOk()
        ->assertSee('Pelat Uji')
        ->assertSee('Jenis Material Uji')
        ->assertSee('Jenis pekerjaan dihapus');

    $this->assertDatabaseHas('material_specs', [
        'id' => $data['materialSpec']->id,
        'work_type_id' => $data['workType']->id,
        'deleted_at' => null,
    ]);
});

test('admin dapat membuka form tambah dengan pilihan jenis pekerjaan aktif', function () {
    $data = siapkanMaterialSpec();
    $deletedType = WorkType::create([
        'work_category_id' => $data['category']->id,
        'name' => 'Jenis Terhapus',
    ]);
    $deletedType->delete();

    $this->actingAs(penggunaMaterialSpec())
        ->get(route('admin.material-specs.create'))
        ->assertOk()
        ->assertSee('Jenis Material Uji')
        ->assertDontSee('Jenis Terhapus');
});

test('admin dapat menambahkan material dengan relasi jenis pekerjaan', function () {
    $data = siapkanMaterialSpec();

    $this->actingAs(penggunaMaterialSpec())
        ->post(route('admin.material-specs.store'), [
            'work_type_id' => $data['workType']->id,
            'name' => 'Pipa Baja',
        ])
        ->assertRedirect(route('admin.material-specs.index'))
        ->assertSessionHas('status', 'Material/spesifikasi berhasil ditambahkan.');

    $this->assertDatabaseHas('material_specs', [
        'work_type_id' => $data['workType']->id,
        'name' => 'Pipa Baja',
        'deleted_at' => null,
    ]);
});

test('admin dapat memakai nama material yang sama pada jenis pekerjaan lain', function () {
    $data = siapkanMaterialSpec();
    $anotherType = WorkType::create([
        'work_category_id' => $data['category']->id,
        'name' => 'Jenis Material Lain',
    ]);

    $this->actingAs(penggunaMaterialSpec())
        ->post(route('admin.material-specs.store'), [
            'work_type_id' => $anotherType->id,
            'name' => 'Pelat Uji',
        ])
        ->assertRedirect(route('admin.material-specs.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseCount('material_specs', 2);
});

test('admin tidak dapat menambahkan material tanpa nama', function () {
    $data = siapkanMaterialSpec();

    $this->actingAs(penggunaMaterialSpec())
        ->from(route('admin.material-specs.create'))
        ->post(route('admin.material-specs.store'), [
            'work_type_id' => $data['workType']->id,
            'name' => '',
        ])
        ->assertRedirect(route('admin.material-specs.create'))
        ->assertSessionHasErrors('name');
});

test('admin harus memilih jenis pekerjaan aktif yang valid', function () {
    $data = siapkanMaterialSpec();
    $deletedType = WorkType::create([
        'work_category_id' => $data['category']->id,
        'name' => 'Jenis Terhapus',
    ]);
    $deletedType->delete();

    $this->actingAs(penggunaMaterialSpec())
        ->from(route('admin.material-specs.create'))
        ->post(route('admin.material-specs.store'), ['name' => 'Pipa Baja'])
        ->assertRedirect(route('admin.material-specs.create'))
        ->assertSessionHasErrors('work_type_id');

    $this->actingAs(penggunaMaterialSpec())
        ->from(route('admin.material-specs.create'))
        ->post(route('admin.material-specs.store'), [
            'work_type_id' => $deletedType->id,
            'name' => 'Pipa Baja',
        ])
        ->assertRedirect(route('admin.material-specs.create'))
        ->assertSessionHasErrors('work_type_id');
});

test('admin dapat mengedit material dan mempertahankan parent yang sudah soft delete', function () {
    $data = siapkanMaterialSpec();
    $data['workType']->delete();

    $this->actingAs(penggunaMaterialSpec())
        ->get(route('admin.material-specs.edit', $data['materialSpec']))
        ->assertOk()
        ->assertSee('Jenis Material Uji (dihapus)');

    $this->actingAs(penggunaMaterialSpec())
        ->put(route('admin.material-specs.update', $data['materialSpec']), [
            'work_type_id' => $data['workType']->id,
            'name' => 'Pelat Uji Diperbarui',
        ])
        ->assertRedirect(route('admin.material-specs.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('material_specs', [
        'id' => $data['materialSpec']->id,
        'work_type_id' => $data['workType']->id,
        'name' => 'Pelat Uji Diperbarui',
        'deleted_at' => null,
    ]);
});

test('admin tidak dapat memindahkan material ke jenis pekerjaan yang sudah soft delete', function () {
    $data = siapkanMaterialSpec();
    $anotherType = WorkType::create([
        'work_category_id' => $data['category']->id,
        'name' => 'Jenis Tujuan',
    ]);
    $anotherType->delete();

    $this->actingAs(penggunaMaterialSpec())
        ->put(route('admin.material-specs.update', $data['materialSpec']), [
            'work_type_id' => $anotherType->id,
            'name' => 'Pelat Uji',
        ])
        ->assertSessionHasErrors('work_type_id');
});

test('admin dapat menghapus material secara soft delete tanpa menghapus Tarif', function () {
    $data = siapkanMaterialSpec();
    $tariff = siapkanTarifMaterialSpec($data['materialSpec']);

    $this->actingAs(penggunaMaterialSpec())
        ->delete(route('admin.material-specs.destroy', $data['materialSpec']))
        ->assertRedirect(route('admin.material-specs.index'))
        ->assertSessionHas('status', 'Material/spesifikasi berhasil dihapus.');

    $this->assertSoftDeleted('material_specs', ['id' => $data['materialSpec']->id]);
    $this->assertDatabaseHas('tariffs', [
        'id' => $tariff->id,
        'material_spec_id' => $data['materialSpec']->id,
    ]);
});

test('menghapus jenis pekerjaan secara soft delete tidak menghapus material anak', function () {
    $data = siapkanMaterialSpec();

    $data['workType']->delete();

    $this->assertSoftDeleted('work_types', ['id' => $data['workType']->id]);
    $this->assertDatabaseHas('material_specs', [
        'id' => $data['materialSpec']->id,
        'work_type_id' => $data['workType']->id,
        'deleted_at' => null,
    ]);
});

test('soft delete material tidak mengubah snapshot item opnam yang memakai Tarifnya', function () {
    $data = siapkanMaterialSpec();
    $tariff = siapkanTarifMaterialSpec($data['materialSpec']);
    $admin = penggunaMaterialSpec();
    $owner = Owner::create(['name' => 'Owner Material']);
    $ship = Ship::create([
        'owner_id' => $owner->id,
        'name' => 'Kapal Material',
        'hull_number' => 'MS-001',
    ]);
    $opnam = Opnam::create([
        'opnam_number' => 'OP-MS-001',
        'ship_id' => $ship->id,
        'created_by' => $admin->id,
        'status' => 'draft',
        'tanggal_opnam' => '2026-01-10',
        'total_biaya' => 5000,
    ]);
    $item = OpnamItem::create([
        'opnam_id' => $opnam->id,
        'tariff_id' => $tariff->id,
        'volume' => 2,
        'unit_price' => 2500,
        'subtotal' => 5000,
        'sort_order' => 1,
    ]);

    $this->actingAs(penggunaMaterialSpec())
        ->delete(route('admin.material-specs.destroy', $data['materialSpec']))
        ->assertRedirect(route('admin.material-specs.index'));

    $this->assertSoftDeleted('material_specs', ['id' => $data['materialSpec']->id]);
    $this->assertDatabaseHas('tariffs', [
        'id' => $tariff->id,
        'material_spec_id' => $data['materialSpec']->id,
    ]);
    $this->assertDatabaseHas('opnam_items', [
        'id' => $item->id,
        'tariff_id' => $tariff->id,
        'unit_price' => 2500,
        'subtotal' => 5000,
    ]);
});

test('pengguna non-admin tidak dapat mengakses pengelolaan material', function () {
    $petugas = penggunaMaterialSpec('petugas');
    $direksi = penggunaMaterialSpec('direksi');
    $data = siapkanMaterialSpec();

    foreach ([$petugas, $direksi] as $user) {
        $this->actingAs($user)
            ->get(route('admin.material-specs.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.material-specs.create'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.material-specs.store'), [
                'work_type_id' => $data['workType']->id,
                'name' => 'Percobaan',
            ])
            ->assertForbidden();

        $this->actingAs($user)
            ->put(route('admin.material-specs.update', $data['materialSpec']), [
                'work_type_id' => $data['workType']->id,
                'name' => 'Diubah',
            ])
            ->assertForbidden();

        $this->actingAs($user)
            ->delete(route('admin.material-specs.destroy', $data['materialSpec']))
            ->assertForbidden();
    }
});
