<?php

namespace Database\Seeders;

use App\Models\WorkCategory;
use Illuminate\Database\Seeder;
use RuntimeException;

class WorkTypeSeeder extends Seeder
{
    public function run(): void
    {
        $workTypesByCategory = [
            'Pelayanan Umum' => [
                'Periksa bebas gas per tangki',
                'Pembuangan sampah kapal per hari',
                'Pemadam kebakaran',
                'Pelayanan air',
                'Pelayanan listrik',
                'Penjagaan Keamanan Selama kapal docking / floating',
                'Pelayanan Telephon lokal',
                'Pelayanan Saluran Ventilasi',
                'Peranca/m3, pemasangan di',
                'Pelayanan Derek',
                'Penundaan Kapal di daerah Perairan Galangan',
                'Pelayanan PANDU per gerakan di daerah Galangan',
            ],
            'Tarif Pengedokan' => [
                'Assistensi Naik & Turun Dock',
                'Docking & Undocking',
                'Docking Per Hari',
                'Docking Report',
            ],
            'Bangunan Kapal' => [
                'Pembersihan badan kapal',
                'Pengecatan (cat owner supplied)',
                'Penggantian baru plat baja/plat lurus',
                'Pembersihan Tangki Air Tawar',
                'Pembersihan Tangki Minyak',
                'Pembersihan Tangki Muatan, Tangki Void, Tangki Sewage',
                'Bongkar pasang manhole',
                'Bongkar pasang bottom Plugs',
                'Pembuangan sludge',
                'Pengetesan tangki / Pressure Test',
                'Hose Test dan Water Test',
                'Pembersihan dan Pengecatan Tank Top, Bilge Space',
                'Penggantian Zinc Anoda',
                'Pembersihan Sea Chest',
                'Pengelasan Fitting',
                'Pengelasan Sambungan',
            ],
            'Mesin Penggerak Utama' => [
                'Perbaikan general overhaul mesin kapal',
                'Pengetesan Keretakan pada Shaft',
                'Megger Test pada MSB',
            ],
            'Sistem Propulsi' => [
                'Bongkar/Pasang dan Pembersihan Daun Baling-baling',
                'Poles dan Varnish Daun Baling-baling',
                'Melamak Surface Contact Daun Baling-baling terhadap Konis Poros',
                'Balanseren Statis Baling-baling',
                'Pengukuran Kelonggaran Poros Baling-baling',
                'Cabut Poros Baling-baling',
                'Ganti Gelang Pengamanan (Skerm)',
                'Bongkar dan Pasang Stern Tube dan Bracket',
                'Penggantian Bantalan Dukung Poros Baling-baling',
                'Kemudi',
            ],
            'Sistem Minyak' => [
                'Ganti baru pipa lurus per meter',
                'Penggantian Klem Pipa',
                'Penggantian Plendes',
                'Penggantian Elbow, Potongan-T',
                'Bengkokan (Bending) per Bengkokan (Untuk Pekerjaan Pipa Putih)',
            ],
            'Sistem Air Laut' => [
                'Katup-katup air laut (Kran-kran laut)',
            ],
            'Jangkar, Rantai Jangkar dan Ceruk Rantai' => [
                'Jangkar',
                'Penggantian rantai Jangkar (Rantai ex. pemilik) per segel',
                'Ceruk rantai (Chain Lockers)',
            ],
        ];

        $expectedCounts = [
            'Pelayanan Umum' => 12,
            'Tarif Pengedokan' => 4,
            'Bangunan Kapal' => 16,
            'Mesin Penggerak Utama' => 3,
            'Sistem Propulsi' => 10,
            'Sistem Minyak' => 5,
            'Sistem Air Laut' => 1,
            'Jangkar, Rantai Jangkar dan Ceruk Rantai' => 3,
        ];

        foreach ($expectedCounts as $categoryName => $expectedCount) {
            if (count($workTypesByCategory[$categoryName] ?? []) !== $expectedCount) {
                throw new RuntimeException("Seeder dihentikan: jumlah WorkType tidak sesuai untuk kategori {$categoryName}.");
            }
        }

        $totalExpected = array_sum($expectedCounts);

        if ($totalExpected !== 54) {
            throw new RuntimeException('Seeder dihentikan: inventarisasi harus berjumlah 54 WorkType.');
        }

        $categories = WorkCategory::withTrashed()
            ->whereIn('name', array_keys($workTypesByCategory))
            ->get()
            ->keyBy('name');

        foreach ($workTypesByCategory as $categoryName => $names) {
            $category = $categories->get($categoryName);

            if ($category === null || $category->trashed() || ! $category->is_active) {
                throw new RuntimeException(
                    "Seeder dihentikan: kategori {$categoryName} tidak ditemukan, nonaktif, atau soft-deleted.",
                );
            }

            $softDeletedNames = $category->workTypes()
                ->withTrashed()
                ->whereIn('name', $names)
                ->whereNotNull('deleted_at')
                ->pluck('name')
                ->all();

            if ($softDeletedNames !== []) {
                throw new RuntimeException(
                    "Seeder dihentikan: pasangan kategori/nama soft-deleted pada {$categoryName}: "
                    .implode(', ', $softDeletedNames),
                );
            }
        }

        foreach ($workTypesByCategory as $categoryName => $names) {
            $category = $categories->get($categoryName);

            foreach ($names as $name) {
                $category->workTypes()->firstOrCreate(['name' => $name]);
            }
        }
    }
}
