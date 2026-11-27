<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['code' => 'M', 'name' => 'Meter'],
            ['code' => 'M2', 'name' => 'Meter Persegi'],
            ['code' => 'M3', 'name' => 'Meter Kubik'],
            ['code' => 'KG', 'name' => 'Kilogram'],
            ['code' => 'LBR', 'name' => 'Lembar'],
            ['code' => 'PCS', 'name' => 'Buah'],
            ['code' => 'TITIK', 'name' => 'Titik'],
            ['code' => 'LAYER', 'name' => 'Lapisan'],
            ['code' => 'HP', 'name' => 'Horse Power'],
            ['code' => 'GT', 'name' => 'Gross Tonnage'],
            ['code' => 'JAM', 'name' => 'Jam'],
            ['code' => 'HARI', 'name' => 'Hari'],
            ['code' => 'KAPAL', 'name' => 'Kapal'],
            ['code' => 'POROS', 'name' => 'Poros'],
            ['code' => 'DAUN', 'name' => 'Daun Baling-baling'],
            ['code' => 'SET', 'name' => 'Set'],
            ['code' => 'CERUK', 'name' => 'Ceruk'],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(
                ['code' => $unit['code']],
                ['name' => $unit['name']],
            );
        }
    }
}
