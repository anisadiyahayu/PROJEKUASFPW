<?php

use App\Models\User;
use App\Models\WorkCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function penggunaKategori(string $role = 'admin'): User
{
    static $id = 0;
    $id++;

    return User::create([
        'name' => ucfirst($role),
        'username' => "{$role}-kategori-{$id}",
        'email' => "{$role}-kategori-{$id}@example.com",
        'password' => 'password',
        'role' => $role,
        'is_active' => true,
    ]);
}

test('admin dapat melihat daftar kategori', function () {
    $admin = penggunaKategori();
    WorkCategory::create(['name' => 'Pekerjaan Lambung', 'description' => 'Perbaikan lambung']);

    $this->actingAs($admin)
        ->get(route('admin.work-categories.index'))
        ->assertOk()
        ->assertSee('Pekerjaan Lambung')
        ->assertSee('Perbaikan lambung');
});

test('admin dapat menambahkan kategori dengan deskripsi nullable', function () {
    $this->actingAs(penggunaKategori())
        ->post(route('admin.work-categories.store'), ['name' => 'Pekerjaan Mesin', 'description' => null])
        ->assertRedirect(route('admin.work-categories.index'));

    $this->assertDatabaseHas('work_categories', [
        'name' => 'Pekerjaan Mesin',
        'description' => null,
        'deleted_at' => null,
    ]);
});

test('admin dapat mengedit kategori dan mempertahankan nama record itu sendiri', function () {
    $admin = penggunaKategori();
    $category = WorkCategory::create(['name' => 'Pekerjaan Lambung']);

    $this->actingAs($admin)
        ->put(route('admin.work-categories.update', $category), [
            'name' => 'Pekerjaan Lambung',
            'description' => 'Pembaruan deskripsi',
        ])
        ->assertRedirect(route('admin.work-categories.index'));

    $this->assertDatabaseHas('work_categories', [
        'id' => $category->id,
        'name' => 'Pekerjaan Lambung',
        'description' => 'Pembaruan deskripsi',
    ]);
});

test('admin dapat menghapus kategori dengan soft delete', function () {
    $admin = penggunaKategori();
    $category = WorkCategory::create(['name' => 'Pekerjaan Struktur']);

    $this->actingAs($admin)
        ->delete(route('admin.work-categories.destroy', $category))
        ->assertRedirect(route('admin.work-categories.index'));

    $this->assertSoftDeleted('work_categories', ['id' => $category->id]);
    expect(WorkCategory::withTrashed()->find($category->id))->not->toBeNull();
});

test('nama kategori wajib diisi', function () {
    $this->actingAs(penggunaKategori())
        ->from(route('admin.work-categories.create'))
        ->post(route('admin.work-categories.store'), ['name' => '', 'description' => null])
        ->assertRedirect(route('admin.work-categories.create'))
        ->assertSessionHasErrors('name');
});

test('nama kategori harus unik', function () {
    WorkCategory::create(['name' => 'Pekerjaan Lambung']);

    $this->actingAs(penggunaKategori())
        ->from(route('admin.work-categories.create'))
        ->post(route('admin.work-categories.store'), ['name' => 'Pekerjaan Lambung'])
        ->assertRedirect(route('admin.work-categories.create'))
        ->assertSessionHasErrors('name');
});

test('nama kategori unik saat edit terhadap kategori lain', function () {
    $admin = penggunaKategori();
    $first = WorkCategory::create(['name' => 'Pekerjaan Lambung']);
    $second = WorkCategory::create(['name' => 'Pekerjaan Mesin']);

    $this->actingAs($admin)
        ->put(route('admin.work-categories.update', $second), ['name' => $first->name])
        ->assertSessionHasErrors('name');
});

test('pengguna non-admin tidak dapat mengelola kategori', function () {
    $petugas = penggunaKategori('petugas');
    $direksi = penggunaKategori('direksi');
    $category = WorkCategory::create(['name' => 'Pekerjaan Lambung']);

    foreach ([$petugas, $direksi] as $user) {
        $this->actingAs($user)
            ->get(route('admin.work-categories.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.work-categories.store'), ['name' => 'Percobaan'])
            ->assertForbidden();

        $this->actingAs($user)
            ->put(route('admin.work-categories.update', $category), ['name' => 'Diubah'])
            ->assertForbidden();

        $this->actingAs($user)
            ->delete(route('admin.work-categories.destroy', $category))
            ->assertForbidden();
    }
});
