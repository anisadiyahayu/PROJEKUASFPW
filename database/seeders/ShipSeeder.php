<?php

namespace Database\Seeders;

use App\Models\Owner;
use App\Models\Ship;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hanya kolom yang ada di ERD SHIPS (SKILL.md §1.1):
        // owner_id (FK OWNERS, sinkronkan OWNERS dari owner_name), name,
        // hull_number (UK; di sini diisi salinan imo_number), jenis_kapal
        // (salin ship_type verbatim; nullable; daftar nilai PRD 9 no.18 terbuka).
        // Field di luar ERD tidak disimpan.
        $ships = [
            [
                'name' => 'KM Bahari Express 01',
                'imo_number' => 'IMO9100001',
                'ship_type' => 'Passenger',
                'owner_name' => 'PT Pelayaran Bahari Nusantara',
            ],
            [
                'name' => 'TB Samudra Tunda IV',
                'imo_number' => 'IMO9100002',
                'ship_type' => 'TD',
                'owner_name' => 'PT Samudra Jasa Maritim',
            ],
            [
                'name' => 'MY Serenity Laut',
                'imo_number' => 'IMO9100003',
                'ship_type' => 'Yacht',
                'owner_name' => 'Serenity Marine Pte Ltd',
            ],
            [
                'name' => 'BG Anugerah Trans 302',
                'imo_number' => 'IMO9100004',
                'ship_type' => 'Barge',
                'owner_name' => 'PT Anugerah Bara Trans',
            ],
            [
                'name' => 'KK Dredger Mandiri II',
                'imo_number' => 'IMO9100005',
                'ship_type' => 'Kapal Kerja',
                'owner_name' => 'PT Pengerukan Indonesia',
            ],
            [
                'name' => 'KM Nusantara Logistik',
                'imo_number' => 'IMO9100006',
                'ship_type' => 'Cargo',
                'owner_name' => 'PT Logistik Laut Timur',
            ],
            [
                'name' => 'KMP Dharma Roro IX',
                'imo_number' => 'IMO9100007',
                'ship_type' => 'Ferry Roro',
                'owner_name' => 'PT Dharma Ferry Utama',
            ],
            [
                'name' => 'KM Sinar Kalimantan',
                'imo_number' => 'IMO9100008',
                'ship_type' => 'Cargo',
                'owner_name' => 'PT Sinar Khatulistiwa Line',
            ],
            [
                'name' => 'KRI Teluk Mandar - 542',
                'imo_number' => 'IMO9100009',
                'ship_type' => 'KRI',
                'owner_name' => 'TNI Angkatan Laut',
            ],
            [
                'name' => 'KM Meratus Perdana',
                'imo_number' => 'IMO9100010',
                'ship_type' => 'Cargo',
                'owner_name' => 'PT Meratus Line',
            ],
            [
                'name' => 'MV Pacific Carrier',
                'imo_number' => 'IMO9100011',
                'ship_type' => 'Cargo',
                'owner_name' => 'Pacific Maritime Corp',
            ],
            [
                'name' => 'KM Kelud Jaya',
                'imo_number' => 'IMO9100012',
                'ship_type' => 'Passenger',
                'owner_name' => 'PT Pelayaran Nasional Indonesia',
            ],
            [
                'name' => 'MT Pertamina Gas Utama',
                'imo_number' => 'IMO9100013',
                'ship_type' => 'Cargo',
                'owner_name' => 'PT Pertamina International Shipping',
            ],
            [
                'name' => 'KM Samudera Raya XXI',
                'imo_number' => 'IMO9100014',
                'ship_type' => 'Cargo',
                'owner_name' => 'PT Samudera Indonesia Tbk',
            ],
            [
                'name' => 'MV Ocean Titan',
                'imo_number' => 'IMO9100015',
                'ship_type' => 'Cargo',
                'owner_name' => 'Ocean Titan Shipping Ltd',
            ],
        ];

        DB::transaction(function () use ($ships): void {
            foreach ($ships as $ship) {
                // Sinkronkan korelasi kapal ↔ pemilik: tautkan ke OWNERS existing
                // (tidak membuat duplikat, tidak menimpa address existing).
                $owner = Owner::query()->firstOrCreate(
                    ['name' => $ship['owner_name']],
                    ['address' => null],
                );

                Ship::query()->updateOrCreate(
                    ['hull_number' => $ship['imo_number']],
                    [
                        'owner_id' => $owner->id,
                        'name' => $ship['name'],
                        'jenis_kapal' => $ship['ship_type'],
                    ],
                );
            }
        });
    }
}
