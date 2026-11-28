<?php

use App\Models\User;
use App\Models\WorkCategory;
use App\Models\WorkType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function penggunaJenisPekerjaan(string $role = 'admin'): User
{
    static $id = 0;
    $id++;

    return User::create([
        'name' => ucfirst($role),
        'username' => "{$role}-jenis-{$id}",
        'email' => "{$role}-jenis-{$id}@example.com",
        'password' => 'password',
        'role' => $role,
        'is_active' => true,
    ]);
}

test('admin dapat melihat daftar jenis pekerjaan dan nama kategori', function () {
    $category = WorkCategory::create(['name' => 'Perawatan Kapal']);
    WorkType::create(['work_category_id' => $category->id, 'name' => 'Pengecatan']);

    $this->actingAs(penggunaJenisPekerjaan())
        ->get(route('admin.work-types.index'))
        ->assertOk()
        ->assertSee('Pengecatan')
        ->assertSee('Perawatan Kapal');
});

test('admin dapat membuka form tambah dengan pilihan kategori', function () {
    WorkCategory::create(['name' => 'Perawatan Kapal']);
    WorkCategory::create(['name' => 'Kategori Nonaktif', 'is_active' => false]);

    $this->actingAs(penggunaJenisPekerjaan())
        ->get(route('admin.work-types.create'))
        ->assertOk()
        ->assertSee('Perawatan Kapal')
        ->assertDontSee('Kategori Nonaktif');
});

test('backend menolak kategori nonaktif untuk jenis pekerjaan baru', function () {
    $category = WorkCategory::create([
        'name' => 'Kategori Tidak Dapat Dipilih',
        'is_active' => false,
    ]);

    $this->actingAs(penggunaJenisPekerjaan())
        ->from(route('admin.work-types.create'))
        ->post(route('admin.work-types.store'), [
            'name' => 'Jenis Baru',
            'work_category_id' => $category->id,
        ])
        ->assertRedirect(route('admin.work-types.create'))
        ->assertSessionHasErrors('work_category_id');

    $this->assertDatabaseMissing('work_types', [
        'name' => 'Jenis Baru',
        'work_category_id' => $category->id,
    ]);
});

test('kategori nonaktif yang sudah terhubung ditampilkan informatif saat edit tetapi bukan pilihan aktif', function () {
    $category = WorkCategory::create([
        'name' => 'Kategori Lama Nonaktif',
        'is_active' => false,
    ]);
    $workType = WorkType::create([
        'work_category_id' => $category->id,
        'name' => 'Jenis Lama',
    ]);

    $this->actingAs(penggunaJenisPekerjaan())
        ->get(route('admin.work-types.edit', $workType))
        ->assertOk()
        ->assertSee('Kategori saat ini')
        ->assertSee('Kategori Lama Nonaktif')
        ->assertSee('Pilih kategori aktif untuk menyimpan perubahan.');
});

test('kategori yang diaktifkan kembali tersedia pada form jenis pekerjaan baru', function () {
    $admin = penggunaJenisPekerjaan();
    $category = WorkCategory::create([
        'name' => 'Kategori Aktif Kembali',
        'is_active' => false,
    ]);

    $this->actingAs($admin)
        ->put(route('admin.work-categories.update', $category), [
        'name' => $category->name,
        'description' => '',
        'is_active' => '1',
    ])->assertRedirect(route('admin.work-categories.index'));

    $this->actingAs($admin)
        ->get(route('admin.work-types.create'))
        ->assertOk()
        ->assertSee('Kategori Aktif Kembali');
});

test('admin berhasil menambahkan jenis pekerjaan', function () {
    $category = WorkCategory::create(['name' => 'Mesin']);

    $this->actingAs(penggunaJenisPekerjaan())
        ->post(route('admin.work-types.store'), [
            'name' => 'Perbaikan Pompa',
            'work_category_id' => $category->id,
        ])
        ->assertRedirect(route('admin.work-types.index'));

    $this->assertDatabaseHas('work_types', [
        'name' => 'Perbaikan Pompa',
        'work_category_id' => $category->id,
        'deleted_at' => null,
    ]);
});

test('admin berhasil mengedit jenis pekerjaan', function () {
    $category = WorkCategory::create(['name' => 'Mesin']);
    $workType = WorkType::create(['work_category_id' => $category->id, 'name' => 'Perbaikan Lama']);

    $this->actingAs(penggunaJenisPekerjaan())
        ->put(route('admin.work-types.update', $workType), [
            'name' => 'Perbaikan Baru',
            'work_category_id' => $category->id,
        ])
        ->assertRedirect(route('admin.work-types.index'));

    $this->assertDatabaseHas('work_types', [
        'id' => $workType->id,
        'name' => 'Perbaikan Baru',
        'work_category_id' => $category->id,
    ]);
});

test('admin berhasil menghapus jenis pekerjaan secara soft delete', function () {
    $category = WorkCategory::create(['name' => 'Mesin']);
    $workType = WorkType::create(['work_category_id' => $category->id, 'name' => 'Perbaikan Pompa']);

    $this->actingAs(penggunaJenisPekerjaan())
        ->delete(route('admin.work-types.destroy', $workType))
        ->assertRedirect(route('admin.work-types.index'));

    $this->assertSoftDeleted('work_types', ['id' => $workType->id]);
});

test('validasi nama jenis pekerjaan wajib diisi', function () {
    $category = WorkCategory::create(['name' => 'Mesin']);

    $this->actingAs(penggunaJenisPekerjaan())
        ->from(route('admin.work-types.create'))
        ->post(route('admin.work-types.store'), [
            'name' => '',
            'work_category_id' => $category->id,
        ])
        ->assertRedirect(route('admin.work-types.create'))
        ->assertSessionHasErrors('name');
});

test('validasi kategori wajib dipilih dan harus kategori yang valid', function () {
    $admin = penggunaJenisPekerjaan();

    $this->actingAs($admin)
        ->from(route('admin.work-types.create'))
        ->post(route('admin.work-types.store'), ['name' => 'Perbaikan'])
        ->assertSessionHasErrors('work_category_id');

    $deletedCategory = WorkCategory::create(['name' => 'Kategori Terhapus']);
    $deletedCategory->delete();

    $this->actingAs($admin)
        ->from(route('admin.work-types.create'))
        ->post(route('admin.work-types.store'), [
            'name' => 'Perbaikan',
            'work_category_id' => $deletedCategory->id,
        ])
        ->assertSessionHasErrors('work_category_id');
});

test('pengguna non-admin tidak dapat mengakses fitur jenis pekerjaan', function () {
    $petugas = penggunaJenisPekerjaan('petugas');
    $direksi = penggunaJenisPekerjaan('direksi');
    $category = WorkCategory::create(['name' => 'Mesin']);
    $workType = WorkType::create(['work_category_id' => $category->id, 'name' => 'Perbaikan']);

    foreach ([$petugas, $direksi] as $user) {
        $this->actingAs($user)
            ->get(route('admin.work-types.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.work-types.create'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.work-types.store'), [
                'name' => 'Percobaan',
                'work_category_id' => $category->id,
            ])
            ->assertForbidden();

        $this->actingAs($user)
            ->put(route('admin.work-types.update', $workType), [
                'name' => 'Diubah',
                'work_category_id' => $category->id,
            ])
            ->assertForbidden();

        $this->actingAs($user)
            ->delete(route('admin.work-types.destroy', $workType))
            ->assertForbidden();
    }
});
