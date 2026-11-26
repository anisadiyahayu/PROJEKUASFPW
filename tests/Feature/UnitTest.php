<?php

use App\Models\MaterialSpec;
use App\Models\Tariff;
use App\Models\Unit;
use App\Models\User;
use App\Models\WorkCategory;
use App\Models\WorkType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function penggunaUnit(string $role = 'admin'): User
{
    static $id = 0;
    $id++;

    return User::create([
        'name' => ucfirst($role),
        'username' => "{$role}-unit-{$id}",
        'email' => "{$role}-unit-{$id}@example.com",
        'password' => 'password',
        'role' => $role,
        'is_active' => true,
    ]);
}

function buatTarifUntukUnit(Unit $unit): Tariff
{
    $category = WorkCategory::create(['name' => 'Kategori Uji Unit']);
    $workType = WorkType::create([
        'work_category_id' => $category->id,
        'name' => 'Jenis Uji Unit',
    ]);
    $material = MaterialSpec::create([
        'work_type_id' => $workType->id,
        'name' => 'Material Uji Unit',
    ]);

    return Tariff::create([
        'material_spec_id' => $material->id,
        'unit_id' => $unit->id,
        'created_by' => penggunaUnit()->id,
        'price' => 1250,
        'effective_from' => '2026-01-01',
    ]);
}

test('admin dapat membuka daftar satuan', function () {
    Unit::create(['code' => 'M2', 'name' => 'Meter persegi']);

    $this->actingAs(penggunaUnit())
        ->get(route('admin.units.index'))
        ->assertOk()
        ->assertSee('Master Satuan')
        ->assertSee('M2')
        ->assertSee('Meter persegi');
});

test('admin dapat menambahkan satuan', function () {
    $this->actingAs(penggunaUnit())
        ->post(route('admin.units.store'), [
            'code' => 'M2',
            'name' => 'Meter persegi',
        ])
        ->assertRedirect(route('admin.units.index'))
        ->assertSessionHas('status', 'Satuan berhasil ditambahkan.');

    $this->assertDatabaseHas('units', [
        'code' => 'M2',
        'name' => 'Meter persegi',
    ]);
});

test('admin tidak dapat menambahkan kode satuan duplikat', function () {
    Unit::create(['code' => 'M2', 'name' => 'Meter persegi']);

    $this->actingAs(penggunaUnit())
        ->from(route('admin.units.create'))
        ->post(route('admin.units.store'), [
            'code' => 'M2',
            'name' => 'Nama lainnya',
        ])
        ->assertRedirect(route('admin.units.create'))
        ->assertSessionHasErrors('code');

    $this->assertDatabaseCount('units', 1);
});

test('admin dapat menggunakan nama yang sama untuk satuan berbeda', function () {
    Unit::create(['code' => 'M2', 'name' => 'Unit ukuran']);

    $this->actingAs(penggunaUnit())
        ->post(route('admin.units.store'), [
            'code' => 'CM2',
            'name' => 'Unit ukuran',
        ])
        ->assertRedirect(route('admin.units.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseCount('units', 2);
});

test('admin tidak dapat menyimpan satuan tanpa kode', function () {
    $this->actingAs(penggunaUnit())
        ->from(route('admin.units.create'))
        ->post(route('admin.units.store'), [
            'code' => '',
            'name' => 'Meter persegi',
        ])
        ->assertRedirect(route('admin.units.create'))
        ->assertSessionHasErrors('code');
});

test('admin tidak dapat menyimpan satuan tanpa nama', function () {
    $this->actingAs(penggunaUnit())
        ->from(route('admin.units.create'))
        ->post(route('admin.units.store'), [
            'code' => 'M2',
            'name' => '',
        ])
        ->assertRedirect(route('admin.units.create'))
        ->assertSessionHasErrors('name');
});

test('admin dapat mengedit satuan', function () {
    $unit = Unit::create(['code' => 'M2-LAMA', 'name' => 'Nama lama']);

    $this->actingAs(penggunaUnit())
        ->put(route('admin.units.update', $unit), [
            'code' => 'M2-BARU',
            'name' => 'Nama baru',
        ])
        ->assertRedirect(route('admin.units.index'))
        ->assertSessionHas('status', 'Satuan berhasil diperbarui.');

    $this->assertDatabaseHas('units', [
        'id' => $unit->id,
        'code' => 'M2-BARU',
        'name' => 'Nama baru',
    ]);
});

test('validasi kode unik mengizinkan kode milik satuan yang sedang diedit', function () {
    $unit = Unit::create(['code' => 'M2', 'name' => 'Meter persegi']);

    $this->actingAs(penggunaUnit())
        ->put(route('admin.units.update', $unit), [
            'code' => 'M2',
            'name' => 'Meter kuadrat',
        ])
        ->assertRedirect(route('admin.units.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('units', [
        'id' => $unit->id,
        'code' => 'M2',
        'name' => 'Meter kuadrat',
    ]);
});

test('admin dapat menghapus satuan yang belum digunakan tarif', function () {
    $unit = Unit::create(['code' => 'M2', 'name' => 'Meter persegi']);

    $this->actingAs(penggunaUnit())
        ->delete(route('admin.units.destroy', $unit))
        ->assertRedirect(route('admin.units.index'))
        ->assertSessionHas('status', 'Satuan berhasil dihapus.');

    $this->assertDatabaseMissing('units', ['id' => $unit->id]);
});

test('admin tidak dapat menghapus satuan yang digunakan tarif', function () {
    $unit = Unit::create(['code' => 'M2', 'name' => 'Meter persegi']);
    $tariff = buatTarifUntukUnit($unit);

    $this->actingAs(penggunaUnit())
        ->delete(route('admin.units.destroy', $unit))
        ->assertRedirect(route('admin.units.index'))
        ->assertSessionHas('error', 'Satuan tidak dapat dihapus karena masih digunakan oleh data tarif.');

    $this->assertDatabaseHas('units', ['id' => $unit->id]);
    $this->assertDatabaseHas('tariffs', ['id' => $tariff->id, 'unit_id' => $unit->id]);
});

test('pengguna non-admin tidak dapat mengakses pengelolaan satuan', function () {
    $petugas = penggunaUnit('petugas');
    $direksi = penggunaUnit('direksi');
    $unit = Unit::create(['code' => 'M2', 'name' => 'Meter persegi']);

    foreach ([$petugas, $direksi] as $user) {
        $this->actingAs($user)
            ->get(route('admin.units.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.units.create'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.units.store'), ['code' => 'M', 'name' => 'Meter'])
            ->assertForbidden();

        $this->actingAs($user)
            ->put(route('admin.units.update', $unit), ['code' => 'M2', 'name' => 'Diubah'])
            ->assertForbidden();

        $this->actingAs($user)
            ->delete(route('admin.units.destroy', $unit))
            ->assertForbidden();
    }
});
