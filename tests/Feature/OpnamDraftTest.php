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
<<<<<<< HEAD
=======
use App\Services\OpnamNumberGenerator;
>>>>>>> d84cf2129cb7286d690c39c169d2bc78fcf194c3
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
<<<<<<< HEAD
        ->assertDontSee('name="catatan"');
=======
        ->assertSee('name="catatan"', false);
>>>>>>> d84cf2129cb7286d690c39c169d2bc78fcf194c3

    $this->post(route('petugas.opnams.store-step'), [
        'step' => 1,
        'ship_id' => $data['ship']->id,
        'tanggal_opnam' => '2026-06-25',
        'lokasi' => 'Dock 02 — Floating Dock',
<<<<<<< HEAD
=======
        'catatan' => 'Catatan uji opnam',
>>>>>>> d84cf2129cb7286d690c39c169d2bc78fcf194c3
    ])->assertRedirect(route('petugas.opnams.create', ['step' => 2]));

    $this->post(route('petugas.opnams.store-step'), [
        'step' => 2,
        'items' => [[
            'uid' => 'item-a',
            'work_category_id' => $data['category']->id,
            'work_type_id' => $data['type']->id,
            'material_spec_id' => $data['material']->id,
            'unit_id' => $data['unit']->id,
            'volume' => 2,
            'ketebalan' => 12,
            'kompleksitas' => 'high',
            'catatan_tambahan' => 'Mohon lengkapi foto kondisi pelat.',
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
<<<<<<< HEAD
        ->and((float) $opnam->adjustment_amount)->toBe(500.0)
        ->and((float) $opnam->total_biaya)->toBe(2500.0)
        ->and($opnam->lokasi)->toBe('Dock 02 — Floating Dock')
        ->and($opnam->catatan)->toBeNull();

    $item = OpnamItem::firstOrFail();
    expect((float) $item->unit_price)->toBe(1000.0)
        ->and((float) $item->subtotal)->toBe(2000.0)
        ->and((float) $item->faktor)->toBe(1.25)
        ->and($item->kompleksitas)->toBe('high')
        ->and($item->ketebalan)->toBe(12)
        ->and($item->catatan_tambahan)->toBe('Mohon lengkapi foto kondisi pelat.')
        ->and($item->lokasi)->toBe('Dock 02 — Floating Dock')
        ->and($item->tariff_id)->toBe($data['tariff']->id);
=======
        ->and((float) $opnam->total_biaya)->toBe(2300.0)
        ->and($opnam->catatan)->toBe('Catatan uji opnam')
        ->and($opnam->jenis_kapal)->toBe('Kapal Kargo');

    $item = OpnamItem::firstOrFail();
    expect((float) $item->unit_price)->toBe(1000.0)
        ->and((float) $item->subtotal)->toBe(2300.0)
        ->and($item->lokasi)->toBe('Dock 02 — Floating Dock')
        ->and($item->tariff_id)->toBe($data['tariff']->id)
        ->and((float) $item->faktor)->toBe(1.15)
        ->and((float) $item->faktor_ketebalan)->toBe(1.0)
        ->and((float) $item->faktor_kompleksitas)->toBe(1.15)
        ->and($item->ketebalan)->toBe(12)
        ->and($item->kompleksitas)->toBe('high')
        ->and($item->catatan_tambahan)->toBe('Mohon lengkapi foto kondisi pelat.');
});

test('faktor tersimpan sebagai snapshot dan tidak berubah saat konfigurasi diubah', function () {
    config()->set('opnam.faktor_ketebalan.12', 1.2);
    config()->set('opnam.faktor_kompleksitas.high', 1.1);

    $data = siapkanDataOpnam();
    $this->actingAs($data['petugas']);

    $this->post(route('petugas.opnams.store-step'), [
        'step' => 1, 'ship_id' => $data['ship']->id, 'tanggal_opnam' => '2026-06-25',
        'lokasi' => 'Dock 01 — Graving Dock',
    ]);
    $this->post(route('petugas.opnams.store-step'), [
        'step' => 2,
        'items' => [[
            'uid' => 'item-a',
            'work_category_id' => $data['category']->id,
            'work_type_id' => $data['type']->id,
            'material_spec_id' => $data['material']->id,
            'unit_id' => $data['unit']->id,
            'volume' => 10,
            'ketebalan' => 12,
            'kompleksitas' => 'high',
            'description' => 'Penggantian pelat',
        ]],
    ]);
    $this->post(route('petugas.opnams.store-step'), ['step' => 3]);
    $this->post(route('petugas.opnams.store-step'), ['step' => 4]);
    $this->post(route('petugas.opnams.store-step'), ['step' => 5]);

    $item = OpnamItem::firstOrFail();
    $opnam = Opnam::firstOrFail();
    expect((float) $item->subtotal)->toBe(13200.0)
        ->and((float) $item->faktor)->toBe(1.32)
        ->and((float) $item->faktor_ketebalan)->toBe(1.2)
        ->and((float) $item->faktor_kompleksitas)->toBe(1.1)
        ->and((float) $opnam->total_biaya)->toBe(13200.0);

    config()->set('opnam.faktor_ketebalan.12', 9);
    config()->set('opnam.faktor_kompleksitas.high', 9);

    expect((float) $item->fresh()->subtotal)->toBe(13200.0)
        ->and((float) $item->fresh()->faktor)->toBe(1.32)
        ->and((float) $item->fresh()->faktor_ketebalan)->toBe(1.2)
        ->and((float) $item->fresh()->faktor_kompleksitas)->toBe(1.1)
        ->and((float) $opnam->fresh()->total_biaya)->toBe(13200.0);
>>>>>>> d84cf2129cb7286d690c39c169d2bc78fcf194c3
});

test('bukti pekerjaan divalidasi format dan ukurannya', function () {
    Storage::fake('public');
    $data = siapkanDataOpnam();
    $this->actingAs($data['petugas']);

    $this->post(route('petugas.opnams.store-step'), [
        'step' => 4,
        'bukti' => ['item-a' => [UploadedFile::fake()->create('bukti.txt', 100, 'text/plain')]],
    ])->assertSessionHasErrors('bukti.item-a.0');

    $this->post(route('petugas.opnams.store-step'), [
        'step' => 4,
        'bukti' => ['item-a' => [UploadedFile::fake()->create('bukti.pdf', 1024 * 11, 'application/pdf')]],
    ])->assertSessionHasErrors('bukti.item-a.0');
});

test('tiap bukti tersimpan dan terhubung ke item yang benar', function () {
    Storage::fake('public');
    $data = siapkanDataOpnam();
    $this->actingAs($data['petugas']);

    $this->post(route('petugas.opnams.store-step'), [
        'step' => 1, 'ship_id' => $data['ship']->id, 'tanggal_opnam' => '2026-06-25',
        'lokasi' => 'Dock 01 — Graving Dock',
    ]);
    $this->post(route('petugas.opnams.store-step'), [
        'step' => 2,
        'items' => [
            [
                'uid' => 'item-a',
                'work_category_id' => $data['category']->id,
                'work_type_id' => $data['type']->id,
                'material_spec_id' => $data['material']->id,
                'unit_id' => $data['unit']->id,
                'volume' => 1, 'kompleksitas' => 'low', 'description' => 'Inspeksi A',
            ],
            [
                'uid' => 'item-b',
                'work_category_id' => $data['category']->id,
                'work_type_id' => $data['type']->id,
                'material_spec_id' => $data['material']->id,
                'unit_id' => $data['unit']->id,
                'volume' => 2, 'kompleksitas' => 'high', 'description' => 'Inspeksi B',
            ],
        ],
    ]);
    $this->post(route('petugas.opnams.store-step'), ['step' => 3]);
    $this->post(route('petugas.opnams.store-step'), [
        'step' => 4,
        'bukti' => [
            'item-a' => [UploadedFile::fake()->create('a.jpg', 100, 'image/jpeg')],
            'item-b' => [UploadedFile::fake()->create('b.pdf', 100, 'application/pdf')],
        ],
    ]);
    $this->post(route('petugas.opnams.store-step'), ['step' => 5]);

    $items = OpnamItem::orderBy('sort_order')->get();
    $attachments = OpnamAttachment::orderBy('id')->get();

    expect($attachments)->toHaveCount(2)
        ->and($attachments[0]->opnam_item_id)->toBe($items[0]->id)
        ->and($attachments[0]->original_name)->toBe('a.jpg')
        ->and($attachments[1]->opnam_item_id)->toBe($items[1]->id)
        ->and($attachments[1]->original_name)->toBe('b.pdf')
        ->and($attachments[0]->uploaded_by)->toBe($data['petugas']->id);

    Storage::disk('public')->assertExists($attachments[0]->file_path);
    Storage::disk('public')->assertExists($attachments[1]->file_path);
});

test('semua langkah wizard dapat dirender', function () {
    $data = siapkanDataOpnam();
    $this->actingAs($data['petugas'])->withSession(['opnam_draft' => [
        'ship_id' => $data['ship']->id,
        'tanggal_opnam' => '2026-06-25',
        'lokasi' => null,
        'catatan' => null,
        'items' => [[
            'uid' => 'item-a',
            'work_category_id' => $data['category']->id,
            'work_type_id' => $data['type']->id,
            'material_spec_id' => $data['material']->id,
            'unit_id' => $data['unit']->id,
            'tariff_id' => $data['tariff']->id,
            'volume' => 2,
            'unit_price' => 1000,
            'faktor' => 1,
            'ketebalan' => 12,
            'kompleksitas' => 'low',
            'catatan_tambahan' => 'Catatan uji',
            'subtotal' => 2000,
            'adjustment' => 0,
            'lokasi' => 'Dock 01',
            'description' => 'Penggantian pelat sisi kanan',
            'work_type_name' => 'Penggantian Pelat Lambung',
            'material_name' => 'Marine Plate AH36',
            'unit_name' => 'm²',
        ]],
        'uploads' => [
            'item-a' => [[
                'file_path' => 'opnam-attachments/bukti.jpg',
                'original_name' => 'bukti.jpg',
                'mime_type' => 'image/jpeg',
                'file_size' => 102400,
            ]],
        ],
    ]]);

    foreach ([1, 2, 3, 4, 5] as $step) {
        $this->get(route('petugas.opnams.create', ['step' => $step]))->assertOk();
    }
<<<<<<< HEAD
});

test('kompleksitas medium pada item tambahan dihitung dengan faktor 1,10', function () {
=======

    $this->get(route('petugas.opnams.create', ['step' => 2]))
        ->assertOk()
        ->assertSee('const tanggalOpnamDariDraft = "2026-06-25"', false)
        ->assertSee('String(t.effective_from).slice(0, 10) <= tanggal', false)
        ->assertSee('id="faktor-tambahan">1x', false)
        ->assertDontSee('id="faktor-tambahan">Rp 0', false)
        ->assertSee('"name":"m\\u00b2"', false)
        ->assertDontSee('"name":"unit"', false);
});

test('kompleksitas medium dan low memakai faktor bawaan 1,10x dan 1x', function () {
>>>>>>> d84cf2129cb7286d690c39c169d2bc78fcf194c3
    $data = siapkanDataOpnam();
    $this->actingAs($data['petugas']);

    $this->post(route('petugas.opnams.store-step'), [
        'step' => 1,
        'ship_id' => $data['ship']->id,
        'tanggal_opnam' => '2026-06-25',
        'lokasi' => 'Dock 01 — Graving Dock',
    ]);

    $this->post(route('petugas.opnams.store-step'), [
        'step' => 2,
        'items' => [
            [
                'uid' => 'item-a',
                'work_category_id' => $data['category']->id,
                'work_type_id' => $data['type']->id,
                'material_spec_id' => $data['material']->id,
                'unit_id' => $data['unit']->id,
                'volume' => 10,
                'kompleksitas' => 'medium',
                'description' => 'Item tambahan medium',
            ],
            [
                'uid' => 'item-b',
                'work_category_id' => $data['category']->id,
                'work_type_id' => $data['type']->id,
                'material_spec_id' => $data['material']->id,
                'unit_id' => $data['unit']->id,
                'volume' => 2,
                'kompleksitas' => 'low',
                'description' => 'Item kedua low',
            ],
        ],
    ])->assertRedirect(route('petugas.opnams.create', ['step' => 3]));

    $this->get(route('petugas.opnams.create', ['step' => 4]))
        ->assertOk()
        ->assertSee('bukti[item-a][]')
        ->assertSee('bukti[item-b][]');

    $this->post(route('petugas.opnams.store-step'), ['step' => 3]);
    $this->post(route('petugas.opnams.store-step'), ['step' => 4]);
    $this->post(route('petugas.opnams.store-step'), ['step' => 5]);

    $items = OpnamItem::orderBy('sort_order')->get();
    $opnam = Opnam::firstOrFail();

    expect($items)->toHaveCount(2)
<<<<<<< HEAD
        ->and((float) $items[0]->faktor)->toBe(1.10)
        ->and($items[0]->kompleksitas)->toBe('medium')
        ->and((float) $items[0]->subtotal)->toBe(10000.0)
        ->and((float) $items[1]->faktor)->toBe(1.0)
        ->and($items[1]->kompleksitas)->toBe('low')
        ->and((float) $opnam->adjustment_amount)->toBe(1000.0)
        ->and((float) $opnam->total_biaya)->toBe(13000.0);
});
=======
        ->and((float) $items[0]->subtotal)->toBe(11000.0)
        ->and((float) $items[0]->faktor_kompleksitas)->toBe(1.1)
        ->and((float) $items[1]->subtotal)->toBe(2000.0)
        ->and((float) $items[1]->faktor_kompleksitas)->toBe(1.0)
        ->and((float) $opnam->total_biaya)->toBe(13000.0);
});

test('validasi dan lokasi unggahan mengikuti config opnam', function () {
    Storage::fake('public');
    config()->set('opnam.unggahan.mimes', 'png');
    config()->set('opnam.unggahan.max_kb', 1);
    config()->set('opnam.unggahan.path', 'bukti-config');

    $data = siapkanDataOpnam();
    $this->actingAs($data['petugas']);

    $this->post(route('petugas.opnams.store-step'), [
        'step' => 4,
        'bukti' => ['item-a' => [UploadedFile::fake()->create('bukti.jpg', 100, 'image/jpeg')]],
    ])->assertSessionHasErrors('bukti.item-a.0');

    $this->post(route('petugas.opnams.store-step'), [
        'step' => 4,
        'bukti' => ['item-a' => [UploadedFile::fake()->create('bukti.png', 2, 'image/png')]],
    ])->assertSessionHasErrors('bukti.item-a.0');

    config()->set('opnam.unggahan.max_kb', 10240);

    $this->post(route('petugas.opnams.store-step'), [
        'step' => 1, 'ship_id' => $data['ship']->id, 'tanggal_opnam' => '2026-06-25',
        'lokasi' => 'Dock 01 — Graving Dock',
    ]);
    $this->post(route('petugas.opnams.store-step'), [
        'step' => 2,
        'items' => [[
            'uid' => 'item-a',
            'work_category_id' => $data['category']->id,
            'work_type_id' => $data['type']->id,
            'material_spec_id' => $data['material']->id,
            'unit_id' => $data['unit']->id,
            'volume' => 1, 'kompleksitas' => 'low', 'description' => 'Inspeksi config',
        ]],
    ]);
    $this->post(route('petugas.opnams.store-step'), ['step' => 3]);
    $this->post(route('petugas.opnams.store-step'), [
        'step' => 4,
        'bukti' => ['item-a' => [UploadedFile::fake()->create('bukti.png', 100, 'image/png')]],
    ]);
    $this->post(route('petugas.opnams.store-step'), ['step' => 5]);

    $attachment = OpnamAttachment::firstOrFail();
    expect(str_starts_with($attachment->file_path, 'bukti-config/'))->toBeTrue();
    Storage::disk('public')->assertExists($attachment->file_path);
});

test('generator nomor opnam memakai config dan menghasilkan nomor unik', function () {
    config()->set('opnam.nomor.prefix', 'OPN');
    config()->set('opnam.nomor.panjang_seq', 4);

    $data = siapkanDataOpnam();
    $generator = app(OpnamNumberGenerator::class);
    $atribut = [
        'ship_id' => $data['ship']->id,
        'created_by' => $data['petugas']->id,
        'status' => Opnam::STATUS_DRAFT,
        'tanggal_opnam' => '2026-06-25',
        'total_biaya' => 0,
    ];

    $pertama = $generator->create($atribut);
    $kedua = $generator->create($atribut);

    expect($pertama->opnam_number)->toMatch('/^OPN-\d{4}-0001$/')
        ->and($kedua->opnam_number)->toMatch('/^OPN-\d{4}-0002$/')
        ->and($pertama->opnam_number)->not->toBe($kedua->opnam_number);
});

test('satuan duplikat tetap dikirim supaya label opsi tidak kosong', function () {
    $data = siapkanDataOpnam();
    $unitDuplikat = Unit::create(['code' => 'M2-DUPLIKAT', 'name' => 'm²']);
    Tariff::create([
        'material_spec_id' => $data['material']->id,
        'unit_id' => $unitDuplikat->id,
        'created_by' => $data['petugas']->id,
        'price' => 2000,
        'effective_from' => '2026-01-01',
    ]);

    $this->actingAs($data['petugas'])
        ->get(route('petugas.opnams.create', ['step' => 2]))
        ->assertOk()
        ->assertSee('"id":'.$unitDuplikat->id.',"name":"m\\u00b2"', false);
});
>>>>>>> d84cf2129cb7286d690c39c169d2bc78fcf194c3
