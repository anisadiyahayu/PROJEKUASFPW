<?php

namespace Database\Seeders;

use App\Models\WorkCategory;
use Illuminate\Database\Seeder;
use RuntimeException;

class WorkCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Pelayanan Umum',
            'Tarif Pengedokan',
            'Bangunan Kapal',
            'Mesin Penggerak Utama',
            'Sistem Propulsi',
            'Sistem Minyak',
            'Sistem Air Laut',
            'Jangkar, Rantai Jangkar dan Ceruk Rantai',
        ];

        $softDeletedNames = WorkCategory::withTrashed()
            ->whereIn('name', $categories)
            ->whereNotNull('deleted_at')
            ->pluck('name')
            ->all();

        if ($softDeletedNames !== []) {
            throw new RuntimeException(
                'Seeder dihentikan: kategori berikut sudah soft-deleted: '.implode(', ', $softDeletedNames),
            );
        }

        foreach ($categories as $name) {
            WorkCategory::firstOrCreate(
                ['name' => $name],
                ['is_active' => true],
            );
        }
    }
}
