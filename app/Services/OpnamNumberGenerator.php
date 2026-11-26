<?php

namespace App\Services;

use App\Models\Opnam;
use Illuminate\Support\Facades\DB;

/**
 * Pembuat nomor opnam (PT-06).
 *
 * Format dibaca dari config/opnam.php sehingga bisa diubah tanpa menyentuh
 * kode. Penomoran dan penyimpanan terjadi dalam satu transaksi dengan kunci
 * baris terakhir, sehingga dua draft yang dibuat bersamaan tidak mendapat
 * nomor yang sama. Tidak memakai tabel sequence baru (ERD tidak mengizinkan).
 */
class OpnamNumberGenerator
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Opnam
    {
        return DB::transaction(function () use ($attributes) {
            $attributes['opnam_number'] = $this->nextNumber();

            return Opnam::create($attributes);
        });
    }

    private function nextNumber(): string
    {
        $prefix = (string) config('opnam.nomor.prefix', 'OPN');
        $panjang = max(1, (int) config('opnam.nomor.panjang_seq', 3));
        $tahun = now()->year;
        $pola = "{$prefix}-{$tahun}-%";

        $terakhir = Opnam::where('opnam_number', 'like', $pola)
            ->orderByDesc('opnam_number')
            ->lockForUpdate()
            ->first();

        $seq = 1;
        if ($terakhir) {
            $seq = (int) substr($terakhir->opnam_number, (int) strrpos($terakhir->opnam_number, '-') + 1) + 1;
        }

        return sprintf('%s-%d-%0*d', $prefix, $tahun, $panjang, $seq);
    }
}
