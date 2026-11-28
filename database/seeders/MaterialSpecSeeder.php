<?php

namespace Database\Seeders;

use App\Models\WorkType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MaterialSpecSeeder extends Seeder
{
    public function run(): void
    {
        $propellerDiameters = [
            's/d 1 m',
            '1,01 s/d 1,5 m',
            '1,51 s/d 2 m',
            '2,01 s/d 2,5 m',
            '2,51 s/d 3 m',
            '3,01 s/d 3,5 m',
            '3,51 s/d 4 m',
            '4,01 s/d 4,5 m',
            '4,51 s/d 5 m',
            '5,01 s/d 5,5 m',
            '5,51 s/d 6 m',
        ];

        $workTypesWithSpecs = [
            'Peranca/m3, pemasangan di' => [
                'Lokasi pemasangan: luar ruangan',
                'Lokasi pemasangan: dalam ruangan',
                'Lokasi pemasangan: posisi tinggi',
            ],
            'Pelayanan Derek' => [
                'Floating crane, kapasitas s/d 50 ton/jam',
                'Floating crane, kapasitas s/d 200 ton/jam',
                'Mobile crane, kapasitas 10 ton/jam',
                'Mobile crane, kapasitas 20 s/d 25 ton/jam',
                'Mobile crane, kapasitas 50 s/d 75 ton/jam',
                'Mobile crane, kapasitas 100 ton/jam',
            ],
            'Penggantian baru plat baja/plat lurus' => [
                'Tebal plat s/d 1/4" / 6 mm',
                'Tebal plat 8 mm s/d 12 mm',
                'Tebal plat lebih besar 12 mm',
                'Mild steel',
                'High Tensile Steel, Grade plate type D&E',
                'Doubling plate',
                'Side shell',
                'Bottom',
                'Keel plates',
                'Deck plates',
                'Tank tops',
                'Engine room (side shell and deck)',
                'Bulkhead',
                'Fore and aft peak keel plate',
                'Internals in DBT',
                'Internals in room and casings',
                'Mixed frame',
                'Internal in aft and fore peak',
                'Fore and aft stem (linggi haluan dan buritan)',
                'Bulbows Bow',
                'Internal Engine room & propulsion system (bottom)',
            ],
            'Pembersihan Tangki Air Tawar' => [
                'Tangki Air Tawar di DBT',
                'Tangki Air Tawar di DT',
                'Tangki Air Ballast di DBT',
                'Tangki Air Ballast di DT',
                'Tangki FP & AP',
            ],
            'Pembersihan Tangki Minyak' => [
                'Heavy Oil Tank D.B.T.',
                'Heavy Oil Tank D.T.',
                'Light Oil Tank D.B.T.',
                'Light Oil Tank D.T.',
            ],
            'Pembersihan Tangki Muatan, Tangki Void, Tangki Sewage' => [
                'Tangki Muatan',
                'Tangki Void',
                'Tangki Sewage',
                'Crude oil',
                'Minyak kotor',
                'Chemical product',
            ],
            'Pengetesan tangki / Pressure Test' => [
                'Tangki Double Bottom — air tawar',
                'Tangki Double Bottom — air laut',
                'Deep Tank — air tawar',
                'Deep Tank — air laut',
                'Fore Tank — air tawar',
                'Fore Tank — air laut',
            ],
            'Hose Test dan Water Test' => [
                'Side Scuttle',
                'Pintu Kedap',
                'Plat Lambung',
            ],
            'Penggantian Zinc Anoda' => [
                'Berat Zinc Anoda s/d 8 Kg',
                'Berat Zinc Anoda s/d 10 Kg',
                'Berat Zinc Anoda s/d 12 Kg',
                'Berat Zinc Anoda s/d 16 Kg',
                'Berat Zinc Anoda s/d 18 Kg',
                'Berat Zinc Anoda s/d 22 Kg',
            ],
            'Pembersihan Sea Chest' => [
                'Ukuran diameter s/d 6"',
                'Ukuran diameter s/d 12"',
                'Ukuran diameter s/d 20"',
                'Ukuran diameter s/d 40"',
                'Ukuran diameter lebih besar 40"',
            ],
            'Pengelasan Fitting' => [
                'Diameter 1 cm',
                'Diameter lebih besar 1 cm',
            ],
            'Pengelasan Sambungan' => [
                'Posisi horizontal / vertikal',
                'Posisi overhead',
            ],
            'Perbaikan general overhaul mesin kapal' => [
                'Daya 0 s/d 100 HP',
                'Daya 101 s/d 200 HP',
                'Daya 201 s/d 500 HP',
                'Daya 501 s/d 1.000 HP',
                'Daya 1.001 s/d 2.500 HP',
                'Daya 2.501 s/d 3.500 HP',
                'Daya 3.501 s/d 5.000 HP',
                'Mesin putaran tinggi / V-form',
                'Mesin MTU',
                'Mesin Caterpillar',
                'Mesin MWM',
            ],
            'Bongkar/Pasang dan Pembersihan Daun Baling-baling' => array_map(
                fn (string $diameter): string => "Diameter baling-baling {$diameter}",
                $propellerDiameters,
            ),
            'Poles dan Varnish Daun Baling-baling' => [
                ...array_map(
                    fn (string $diameter): string => "Diameter baling-baling {$diameter}",
                    $propellerDiameters,
                ),
                'Diameter baling-baling lebih dari 6 m',
                'Jumlah daun minimum 3',
                'Jumlah daun 4',
                'Jumlah daun 5',
            ],
            'Melamak Surface Contact Daun Baling-baling terhadap Konis Poros' => array_map(
                fn (string $diameter): string => "Diameter baling-baling {$diameter}",
                $propellerDiameters,
            ),
            'Balanseren Statis Baling-baling' => [
                ...array_map(
                    fn (string $diameter): string => "Diameter baling-baling {$diameter}",
                    $propellerDiameters,
                ),
                'Jumlah daun 3',
                'Jumlah daun 4',
                'Jumlah daun 5',
            ],
            'Pengukuran Kelonggaran Poros Baling-baling' => [
                'Diameter poros s/d 100 mm (Tabung Poros MK & BLK)',
                'Diameter poros 101 s/d 150 mm (Tabung Poros MK & BLK)',
                'Diameter poros 151 s/d 200 mm (Tabung Poros MK & BLK)',
                'Diameter poros 201 s/d 250 mm (Tabung Poros MK & BLK)',
                'Diameter poros 251 s/d 300 mm (Tabung Poros MK & BLK)',
                'Diameter poros 301 s/d 350 mm (Tabung Poros MK & BLK)',
                'Diameter poros 351 s/d 400 mm (Tabung Poros MK & BLK)',
                'Diameter poros 401 s/d 450 mm (Tabung Poros MK & BLK)',
                'Diameter poros 451 s/d 500 mm (Tabung Poros MK & BLK)',
                'Diameter poros 501 s/d 550 mm (Tabung Poros MK & BLK)',
            ],
            'Cabut Poros Baling-baling' => [
                'Diameter poros 100 mm',
                'Diameter poros 150 mm',
                'Diameter poros 200 mm',
                'Diameter poros 250 mm',
                'Diameter poros 300 mm',
                'Diameter poros 350 mm',
                'Diameter poros 400 mm',
                'Diameter poros 450 mm',
                'Diameter poros 500 mm',
                'Diameter poros 550 mm',
                'Diameter poros 600 mm',
                'Panjang poros s/d 5 m',
                'Panjang poros 5,01 s/d 7 m',
                'Panjang poros 7,01 s/d 10 m',
            ],
            'Ganti Gelang Pengamanan (Skerm)' => [
                'Diameter poros s/d 100 mm',
                'Diameter poros s/d 200 mm',
                'Diameter poros s/d 300 mm',
                'Diameter poros s/d 400 mm',
                'Diameter poros s/d 500 mm',
                'Diameter poros s/d 600 mm',
            ],
            'Bongkar dan Pasang Stern Tube dan Bracket' => [
                'Diameter dengan sleeve s/d 50 mm',
                'Diameter dengan sleeve 51 s/d 75 mm',
                'Diameter dengan sleeve 76 s/d 100 mm',
                'Diameter dengan sleeve 101 s/d 150 mm',
                'Diameter dengan sleeve 151 s/d 200 mm',
                'Diameter dengan sleeve 201 s/d 250 mm',
                'Diameter dengan sleeve 256 s/d 300 mm',
                'Diameter dengan sleeve 301 s/d 350 mm',
                'Diameter dengan sleeve 356 s/d 400 mm',
                'Diameter dengan sleeve 401 s/d 450 mm',
                'Diameter dengan sleeve 451 s/d 475 mm',
                'Diameter dengan sleeve 476 s/d 500 mm',
                'Koker Bush per tabung',
                'Bantalan stern tube dan bracket bahan dari karet',
                'Bantalan kayu pok atas & bawah',
                'Bantalan kayu pok bawah',
                'Bahan bantalan babit',
            ],
            'Penggantian Bantalan Dukung Poros Baling-baling' => [
                'Diameter s/d 100 mm',
                'Diameter 101 s/d 200 mm',
                'Diameter 201 s/d 250 mm',
                'Diameter 251 s/d 300 mm',
                'Diameter 301 s/d 350 mm',
                'Diameter 351 s/d 400 mm',
                'Diameter 401 s/d 450 mm',
                'Diameter 451 s/d 500 mm',
                'Diameter 501 s/d 550 mm',
                'Diameter 551 s/d 600 mm',
                'Plummer Block Bearing',
                'Shaft Thrust Bearing',
            ],
            'Kemudi' => [
                'Pintle Kemudi',
                'Kort Nozzle',
                'Kwadran kemudi berikut sistem',
            ],
            'Ganti baru pipa lurus per meter' => [
                'Diameter s/d 1/2"',
                'Diameter s/d 1"',
                'Diameter s/d 2"',
                'Diameter s/d 3"',
                'Diameter s/d 4"',
                'Diameter s/d 5"',
                'Diameter s/d 6"',
                'Diameter s/d 8"',
                'Diameter s/d 10"',
                'Diameter s/d 12"',
                'Jenis pipa hitam',
                'Jenis pipa SCH 40',
                'Jenis pipa SCH 80',
                'Jenis pipa aluminium',
                'Jenis pipa tembaga',
                'Jenis pipa Cuni',
            ],
            'Penggantian Klem Pipa' => [
                'Diameter s/d 1/2"',
                'Diameter 0,55 s/d 1"',
                'Diameter 1,1 s/d 2"',
                'Diameter 2,1 s/d 3"',
                'Diameter 3,1 s/d 4"',
                'Diameter 4,1 s/d 6"',
                'Diameter 6,1 s/d 8"',
                'Diameter 8,1 s/d 10"',
                'Diameter 10,1 s/d 12"',
            ],
            'Penggantian Plendes' => [
                'Diameter s/d 1/2"',
                'Diameter 0,55 s/d 1"',
                'Diameter 1,1 s/d 2"',
                'Diameter 2,1 s/d 3"',
                'Diameter 3,1 s/d 4"',
                'Diameter 4,1 s/d 5"',
                'Diameter 5,1 s/d 6"',
                'Diameter 6,1 s/d 8"',
                'Diameter 8,1 s/d 10"',
                'Diameter 10,1 s/d 12"',
                'Tekanan standar katup s/d 10K / 150 psi',
            ],
            'Penggantian Elbow, Potongan-T' => [
                'Diameter s/d 1/2"',
                'Diameter 1"',
                'Diameter 2"',
                'Diameter 3"',
                'Diameter 4"',
                'Diameter 5"',
                'Diameter 6"',
                'Diameter 8"',
                'Diameter 10"',
                'Diameter 12"',
                'Diameter di atas 12"',
                'Elbow Galvanish',
                'Elbow Steel',
                'Bahan SCH 40',
            ],
            'Bengkokan (Bending) per Bengkokan (Untuk Pekerjaan Pipa Putih)' => [
                'Diameter s/d 1/2"',
                'Diameter 0,55 s/d 1"',
                'Diameter 1,1 s/d 2"',
                'Diameter 2,1 s/d 3"',
                'Diameter 3,1 s/d 4"',
                'Diameter 4,1 s/d 5"',
                'Diameter 5,1 s/d 6"',
                'Diameter 6,1 s/d 8"',
                'Diameter 8,1 s/d 10"',
                'Diameter 10,1 s/d 12"',
                'Jenis pipa putih',
            ],
            'Katup-katup air laut (Kran-kran laut)' => [
                'Diameter s/d 1"',
                'Diameter 1,1 s/d 2"',
                'Diameter 2,1 s/d 3"',
                'Diameter 3,1 s/d 4"',
                'Diameter 4,1 s/d 5"',
                'Diameter 5,1 s/d 6"',
                'Diameter 6,1 s/d 8"',
                'Diameter 8,1 s/d 10"',
                'Diameter 10,1 s/d 12"',
                'Diameter 12,1 s/d 14"',
                'Diameter 14,1 s/d 16"',
                'Globe Valve',
                'Gate Valve',
                'Butterfly Valve',
                'Angle Valve',
                'Stop Valve',
            ],
            'Jangkar' => [
                'Jangkar dengan staldraad',
            ],
        ];

        foreach ($workTypesWithSpecs as $workTypeName => $specNames) {
            if ($specNames === [] || count($specNames) !== count(array_unique($specNames))) {
                throw new RuntimeException(
                    "Seeder dihentikan: daftar spesifikasi kosong atau duplikat untuk {$workTypeName}.",
                );
            }
        }

        $workTypes = WorkType::withTrashed()
            ->whereIn('name', array_keys($workTypesWithSpecs))
            ->get()
            ->groupBy('name');

        foreach ($workTypesWithSpecs as $workTypeName => $specNames) {
            $matches = $workTypes->get($workTypeName, collect());

            if ($matches->count() !== 1 || $matches->first()->trashed()) {
                throw new RuntimeException(
                    "Seeder dihentikan: WorkType {$workTypeName} tidak ditemukan secara tunggal atau soft-deleted.",
                );
            }
        }

        $duplicatePairs = DB::table('material_specs')
            ->select('work_type_id', 'name')
            ->selectRaw('COUNT(*) AS total')
            ->groupBy('work_type_id', 'name')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicatePairs->isNotEmpty()) {
            throw new RuntimeException(
                'Seeder dihentikan: ditemukan pasangan WorkType/spesifikasi duplikat: '
                .$duplicatePairs
                    ->map(fn ($pair) => "{$pair->work_type_id}:{$pair->name} ({$pair->total})")
                    ->implode(', '),
            );
        }

        foreach ($workTypesWithSpecs as $workTypeName => $specNames) {
            $workType = $workTypes->get($workTypeName)->first();
            $softDeletedNames = $workType->materialSpecs()
                ->withTrashed()
                ->whereIn('name', $specNames)
                ->whereNotNull('deleted_at')
                ->pluck('name')
                ->all();

            if ($softDeletedNames !== []) {
                throw new RuntimeException(
                    "Seeder dihentikan: pasangan WorkType/spesifikasi soft-deleted pada {$workTypeName}: "
                    .implode(', ', $softDeletedNames),
                );
            }
        }

        foreach ($workTypesWithSpecs as $workTypeName => $specNames) {
            $workType = $workTypes->get($workTypeName)->first();

            foreach ($specNames as $name) {
                $workType->materialSpecs()->firstOrCreate(['name' => $name]);
            }
        }
    }
}
