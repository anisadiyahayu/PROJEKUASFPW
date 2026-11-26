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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function siapkanDataOpnam(): array
{
    $admin = User::create([
        'name' => 'Admin', 'username' => 'admin', 'email' => 'admin@example.com',
        'password' => 'password', 'role' => 'admin', 'is_active' => true,
    ]);
    $petugas = User::create([
        'name' => 'Petugas', 'username' => 'petugas', 'email' => 'petugas@example.com',
        'password' => 'password', 'role' => 'petugas', 'is_active' => true,
    ]);
    $owner = Owner::create(['name' => 'PT Bahari Sentosa']);
    $ship = Ship::create([
        'owner_id' => $owner->id, 'name' => 'KM Samudra Jaya',
        'hull_number' => 'SJ-0824', 'jenis_kapal' => 'Kapal Kargo',
    ]);
    $category = WorkCategory::create(['name' => 'Pekerjaan Lambung']);
    $type = WorkType::create(['work_category_id' => $category->id, 'name' => 'Penggantian Pelat Lambung']);
    $material = MaterialSpec::create(['work_type_id' => $type->id, 'name' => 'Marine Plate AH36']);
    $unit = Unit::create(['code' => 'm2', 'name' => 'm²']);
    $tariff = Tariff::create([
        'material_spec_id' => $material->id, 'unit_id' => $unit->id, 'created_by' => $admin->id,
        'price' => 1000, 'effective_from' => '2026-01-01',
    ]);

    return compact('petugas', 'ship', 'category', 'type', 'material', 'unit', 'tariff');
}

test('wizard menyimpan draft dengan status draft dan snapshot biaya', function () {
    $data = siapkanDataOpnam();
    $this->actingAs($data['petugas']);

    $this->get(route('petugas.opnams.create'))->assertOk()
        ->assertSee('Buat Opname')
        ->assertSee('Jenis Kapal')
        ->assertSee('Kapal Kargo')
        ->assertSee('Lokasi Pekerjaan')
        ->assertSee('Dock 01 — Graving Dock')
        ->assertSee('Dock 02 — Floating Dock')
        ->assertDontSee('Pilih Lokasi')
        ->assertDontSee('Catatan');

    $this->post(route('petugas.opnams.store-step'), [
        'step' => 1,
        'ship_id' => $data['ship']->id,
        'tanggal_opnam' => '2026-06-25',
        'lokasi' => 'Dock 02 — Floating Dock',
    ])->assertRedirect(route('petugas.opnams.create', ['step' => 2]));

    $this->post(route('petugas.opnams.store-step'), [
        'step' => 2,
        'items' => [[
            'work_category_id' => $data['category']->id,
            'work_type_id' => $data['type']->id,
            'material_spec_id' => $data['material']->id,
            'unit_id' => $data['unit']->id,
            'volume' => 2,
            'faktor' => 1.25,
            'lokasi' => 'Dock 01',
            'description' => 'Penggantian pelat sisi kanan',
        ]],
    ])->assertRedirect(route('petugas.opnams.create', ['step' => 3]));

    $this->post(route('petugas.opnams.store-step'), ['step' => 3])
        ->assertRedirect(route('petugas.opnams.create', ['step' => 4]));

    $this->post(route('petugas.opnams.store-step'), ['step' => 4])
        ->assertRedirect(route('petugas.opnams.create', ['step' => 5]));

    $this->post(route('petugas.opnams.store-step'), ['step' => 5])
        ->assertRedirect(route('petugas.opnams.create'));

    $opnam = Opnam::firstOrFail();
    expect($opnam->status)->toBe(Opnam::STATUS_DRAFT)
        ->and($opnam->opnam_number)->toMatch('/^OPN-\d{4}-\d{3}$/')
        ->and((float) $opnam->adjustment_amount)->toBe(500.0)
        ->and((float) $opnam->total_biaya)->toBe(2500.0)
        ->and($opnam->lokasi)->toBe('Dock 02 — Floating Dock')
        ->and($opnam->catatan)->toBeNull();

    $item = OpnamItem::firstOrFail();
    expect((float) $item->unit_price)->toBe(1000.0)
        ->and((float) $item->subtotal)->toBe(2000.0)
        ->and((float) $item->faktor)->toBe(1.25)
        ->and($item->lokasi)->toBe('Dock 01')
        ->and($item->tariff_id)->toBe($data['tariff']->id);
});

test('bukti pekerjaan divalidasi format dan ukurannya', function () {
    Storage::fake('public');
    $data = siapkanDataOpnam();
    $this->actingAs($data['petugas']);

    $this->post(route('petugas.opnams.store-step'), [
        'step' => 4,
        'bukti' => [UploadedFile::fake()->create('bukti.txt', 100, 'text/plain')],
    ])->assertSessionHasErrors('bukti.0');

    $this->post(route('petugas.opnams.store-step'), [
        'step' => 4,
        'bukti' => [UploadedFile::fake()->create('bukti.pdf', 1024 * 11, 'application/pdf')],
    ])->assertSessionHasErrors('bukti.0');
});

test('lampiran bukti tersimpan dan terhubung ke item', function () {
    Storage::fake('public');
    $data = siapkanDataOpnam();
    $this->actingAs($data['petugas']);

    $this->post(route('petugas.opnams.store-step'), [
        'step' => 1, 'ship_id' => $data['ship']->id, 'tanggal_opnam' => '2026-06-25',
        'lokasi' => 'Dock 01 — Graving Dock',
    ]);
    $this->post(route('petugas.opnams.store-step'), [
        'step' => 2,
        'items' => [[
            'work_category_id' => $data['category']->id,
            'work_type_id' => $data['type']->id,
            'material_spec_id' => $data['material']->id,
            'unit_id' => $data['unit']->id,
            'volume' => 1, 'faktor' => 1, 'lokasi' => 'Dock 01', 'description' => 'Inspeksi lambung',
        ]],
    ]);
    $this->post(route('petugas.opnams.store-step'), ['step' => 3]);
    $this->post(route('petugas.opnams.store-step'), [
        'step' => 4,
        'bukti' => [UploadedFile::fake()->create('bukti.jpg', 100, 'image/jpeg')],
    ]);
    $this->post(route('petugas.opnams.store-step'), ['step' => 5]);

    $attachment = OpnamAttachment::firstOrFail();
    expect($attachment->opnam_item_id)->toBe(OpnamItem::firstOrFail()->id)
        ->and($attachment->original_name)->toBe('bukti.jpg')
        ->and($attachment->uploaded_by)->toBe($data['petugas']->id);
    Storage::disk('public')->assertExists($attachment->file_path);
});

test('semua langkah wizard dapat dirender', function () {
    $data = siapkanDataOpnam();
    $this->actingAs($data['petugas'])->withSession(['opnam_draft' => [
        'ship_id' => $data['ship']->id,
        'tanggal_opnam' => '2026-06-25',
        'lokasi' => null,
        'catatan' => null,
        'items' => [[
            'work_category_id' => $data['category']->id,
            'work_type_id' => $data['type']->id,
            'material_spec_id' => $data['material']->id,
            'unit_id' => $data['unit']->id,
            'tariff_id' => $data['tariff']->id,
            'volume' => 2,
            'unit_price' => 1000,
            'faktor' => 1,
            'subtotal' => 2000,
            'adjustment' => 0,
            'lokasi' => 'Dock 01',
            'description' => 'Penggantian pelat sisi kanan',
            'work_type_name' => 'Penggantian Pelat Lambung',
            'material_name' => 'Marine Plate AH36',
            'unit_name' => 'm²',
        ]],
        'uploads' => [[
            'file_path' => 'opnam-attachments/bukti.jpg',
            'original_name' => 'bukti.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 102400,
        ]],
    ]]);

    foreach ([1, 2, 3, 4, 5] as $step) {
        $this->get(route('petugas.opnams.create', ['step' => $step]))->assertOk();
    }
});
