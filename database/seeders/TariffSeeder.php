<?php

namespace Database\Seeders;

use App\Models\MaterialSpec;
use App\Models\Unit;
use App\Models\User;
use App\Models\WorkType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TariffSeeder extends Seeder
{
    private const EFFECTIVE_FROM = '2019-01-01';

    public function run(): void
    {
        $entries = $this->entries();

        if (count($entries) !== 81) {
            throw new RuntimeException('Seeder dihentikan: inventaris tarif exact-map harus berjumlah 81 entri.');
        }

        $admin = User::query()->where('role', User::ROLE_ADMIN)->orderBy('id')->first();

        if ($admin === null) {
            throw new RuntimeException('Seeder dihentikan: akun admin tidak ditemukan untuk created_by.');
        }

        $workTypes = WorkType::query()
            ->whereIn('name', array_unique(array_column($entries, 'work_type')))
            ->get()
            ->groupBy('name');
        $units = Unit::query()->whereIn('code', array_unique(array_column($entries, 'unit')))
            ->get()->keyBy('code');
        $specs = MaterialSpec::query()
            ->whereIn('name', array_unique(array_column($entries, 'material_spec')))
            ->get()
            ->groupBy(fn (MaterialSpec $spec): string => $spec->workType->name."\0".$spec->name);

        $resolved = [];
        foreach ($entries as $entry) {
            $workTypeMatches = $workTypes->get($entry['work_type'], collect());
            $unit = $units->get($entry['unit']);
            $specMatches = $specs->get($entry['work_type']."\0".$entry['material_spec'], collect());

            if ($workTypeMatches->count() !== 1 || $unit === null || $specMatches->count() !== 1) {
                throw new RuntimeException(
                    "Seeder dihentikan: pemetaan katalog tidak tunggal/tersedia untuk "
                    ."{$entry['work_type']} / {$entry['material_spec']} / {$entry['unit']} "
                    ."(PDF p. {$entry['page']}).",
                );
            }

            $resolved[] = [
                'material_spec_id' => $specMatches->first()->id,
                'unit_id' => $unit->id,
                'created_by' => $admin->id,
                'price' => number_format((float) $entry['price'], 2, '.', ''),
                'effective_from' => self::EFFECTIVE_FROM,
            ];
        }

        $keys = [];
        foreach ($resolved as $row) {
            $key = "{$row['material_spec_id']}:{$row['unit_id']}:{$row['effective_from']}";
            if (isset($keys[$key])) {
                throw new RuntimeException("Seeder dihentikan: unique key ganda di inventaris ({$key}).");
            }
            $keys[$key] = true;
        }

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($resolved, &$created, &$skipped): void {
            foreach ($resolved as $row) {
                $exists = DB::table('tariffs')
                    ->where('material_spec_id', $row['material_spec_id'])
                    ->where('unit_id', $row['unit_id'])
                    ->where('effective_from', $row['effective_from'])
                    ->exists();

                if ($exists) {
                    // Preserve existing immutable tariff, including a differing price.
                    $skipped++;
                    continue;
                }

                DB::table('tariffs')->insert([
                    ...$row,
                    'created_at' => now(),
                ]);
                $created++;
            }
        });

        $this->command?->info("TariffSeeder: dibuat {$created}; dilewati (unique key sudah ada) {$skipped}.");
    }

    /**
     * Harga ditranskripsikan dari tabel visual PDF; nilai disimpan dalam rupiah.
     * page adalah nomor halaman PDF (bukan nomor cetak di dokumen).
     *
     * @return list<array{work_type: string, material_spec: string, unit: string, price: int, page: int}>
     */
    private function entries(): array
    {
        $entries = [];
        $add = static function (string $workType, string $spec, string $unit, int $price, int $page) use (&$entries): void {
            $entries[] = [
                'work_type' => $workType,
                'material_spec' => $spec,
                'unit' => $unit,
                'price' => $price,
                'page' => $page,
            ];
        };

        foreach ([
            ['Lokasi pemasangan: luar ruangan', 31200],
            ['Lokasi pemasangan: dalam ruangan', 50400],
            ['Lokasi pemasangan: posisi tinggi', 64800],
        ] as [$spec, $price]) {
            $add('Peranca/m3, pemasangan di', $spec, 'M3', $price, 2);
        }

        $plateSpecs = [
            ['Tebal plat s/d 1/4" / 6 mm', 34200],
            ['Tebal plat 8 mm s/d 12 mm', 32600],
            ['Tebal plat lebih besar 12 mm', 32600],
        ];
        foreach ($plateSpecs as [$spec, $price]) {
            $add('Penggantian baru plat baja/plat lurus', $spec, 'KG', $price, 6);
        }

        foreach ([
            ['Tangki Air Tawar di DBT', 29000],
            ['Tangki Air Tawar di DT', 23000],
            ['Tangki Air Ballast di DBT', 28000],
            ['Tangki Air Ballast di DT', 21000],
            ['Tangki FP & AP', 30000],
        ] as [$spec, $price]) {
            $add('Pembersihan Tangki Air Tawar', $spec, 'M3', $price, 8);
        }
        foreach ([
            ['Heavy Oil Tank D.B.T.', 57000],
            ['Heavy Oil Tank D.T.', 44000],
            ['Light Oil Tank D.B.T.', 44000],
            ['Light Oil Tank D.T.', 42000],
        ] as [$spec, $price]) {
            $add('Pembersihan Tangki Minyak', $spec, 'M3', $price, 8);
        }
        foreach ([
            ['Tangki Muatan', 29000],
            ['Tangki Void', 16000],
            ['Tangki Sewage', 566000],
        ] as [$spec, $price]) {
            $add('Pembersihan Tangki Muatan, Tangki Void, Tangki Sewage', $spec, 'M3', $price, 8);
        }
        foreach ([
            ['Tangki Double Bottom — air tawar', 85000],
            ['Tangki Double Bottom — air laut', 10800],
            ['Deep Tank — air tawar', 85000],
            ['Deep Tank — air laut', 10800],
            ['Fore Tank — air tawar', 85000],
            ['Fore Tank — air laut', 10800],
        ] as [$spec, $price]) {
            $add('Pengetesan tangki / Pressure Test', $spec, 'M3', $price, 9);
        }
        foreach ([
            ['Side Scuttle', 'PCS', 175500],
            ['Pintu Kedap', 'PCS', 227500],
            ['Plat Lambung', 'LBR', 565500],
        ] as [$spec, $unit, $price]) {
            $add('Hose Test dan Water Test', $spec, $unit, $price, 9);
        }
        foreach ([
            ['Berat Zinc Anoda s/d 8 Kg', 150000],
            ['Berat Zinc Anoda s/d 10 Kg', 175000],
            ['Berat Zinc Anoda s/d 12 Kg', 239000],
            ['Berat Zinc Anoda s/d 16 Kg', 263000],
            ['Berat Zinc Anoda s/d 18 Kg', 302000],
            ['Berat Zinc Anoda s/d 22 Kg', 366000],
        ] as [$spec, $price]) {
            $add('Penggantian Zinc Anoda', $spec, 'PCS', $price, 10);
        }
        foreach ([
            ['Ukuran diameter s/d 6"', 1234000],
            ['Ukuran diameter s/d 12"', 1728000],
            ['Ukuran diameter s/d 20"', 2287000],
            ['Ukuran diameter s/d 40"', 2960000],
            ['Ukuran diameter lebih besar 40"', 3947000],
        ] as [$spec, $price]) {
            $add('Pembersihan Sea Chest', $spec, 'PCS', $price, 10);
        }
        $add('Pengelasan Fitting', 'Diameter 1 cm', 'TITIK', 18000, 10);

        foreach ([
            ['Daya 0 s/d 100 HP', 130900],
            ['Daya 101 s/d 200 HP', 105200],
            ['Daya 201 s/d 500 HP', 82800],
            ['Daya 501 s/d 1.000 HP', 72500],
            ['Daya 1.001 s/d 2.500 HP', 69000],
            ['Daya 2.501 s/d 3.500 HP', 62500],
            ['Daya 3.501 s/d 5.000 HP', 62200],
        ] as [$spec, $price]) {
            $add('Perbaikan general overhaul mesin kapal', $spec, 'HP', $price, 11);
        }
        foreach ([
            ['Diameter poros s/d 100 mm', 1283000],
            ['Diameter poros s/d 200 mm', 1925000],
            ['Diameter poros s/d 300 mm', 2566000],
            ['Diameter poros s/d 400 mm', 4172000],
            ['Diameter poros s/d 500 mm', 5131000],
            ['Diameter poros s/d 600 mm', 6414000],
        ] as [$spec, $price]) {
            $add('Ganti Gelang Pengamanan (Skerm)', $spec, 'POROS', $price, 14);
        }

        $clampPrices = [77000, 95000, 114000, 168000, 226000, 301000, 451000, 667000, 754000];
        $clampSpecs = [
            'Diameter s/d 1/2"', 'Diameter 0,55 s/d 1"', 'Diameter 1,1 s/d 2"',
            'Diameter 2,1 s/d 3"', 'Diameter 3,1 s/d 4"', 'Diameter 4,1 s/d 6"',
            'Diameter 6,1 s/d 8"', 'Diameter 8,1 s/d 10"', 'Diameter 10,1 s/d 12"',
        ];
        foreach ($clampSpecs as $index => $spec) {
            $add('Penggantian Klem Pipa', $spec, 'PCS', $clampPrices[$index], 18);
        }

        $flangePrices = [95000, 119000, 168000, 224000, 319000, 390000, 475000, 642000, 792000, 1007000];
        $flangeSpecs = [
            'Diameter s/d 1/2"', 'Diameter 0,55 s/d 1"', 'Diameter 1,1 s/d 2"',
            'Diameter 2,1 s/d 3"', 'Diameter 3,1 s/d 4"', 'Diameter 4,1 s/d 5"',
            'Diameter 5,1 s/d 6"', 'Diameter 6,1 s/d 8"', 'Diameter 8,1 s/d 10"',
            'Diameter 10,1 s/d 12"',
        ];
        foreach ($flangeSpecs as $index => $spec) {
            $add('Penggantian Plendes', $spec, 'PCS', $flangePrices[$index], 18);
        }

        $bendingSpecs = [
            'Diameter s/d 1/2"', 'Diameter 0,55 s/d 1"', 'Diameter 1,1 s/d 2"',
            'Diameter 2,1 s/d 3"', 'Diameter 3,1 s/d 4"', 'Diameter 4,1 s/d 5"',
            'Diameter 5,1 s/d 6"', 'Diameter 6,1 s/d 8"', 'Diameter 8,1 s/d 10"',
            'Diameter 10,1 s/d 12"',
        ];
        foreach ($bendingSpecs as $index => $spec) {
            $add(
                'Bengkokan (Bending) per Bengkokan (Untuk Pekerjaan Pipa Putih)',
                $spec,
                'PCS',
                [70000, 107000, 152000, 259000, 321000, 369000, 418000, 611000, 1286000, 1504000][$index],
                19,
            );
        }

        return $entries;
    }
}
