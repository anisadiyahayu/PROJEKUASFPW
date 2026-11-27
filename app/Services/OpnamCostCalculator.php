<?php

namespace App\Services;

/**
 * Satu tempat logika perhitungan biaya opnam (PRD 3.10.4, BR-32).
 *
 * Dipanggil oleh form pencatatan (PT-01/PT-05). Pemilik AD-07 dan DR-03
 * akan memanggil service yang sama saat revisi opnam agar hasil identik.
 */
class OpnamCostCalculator
{
    public function faktorKetebalan(mixed $input): float
    {
        if ($input === null || $input === '') {
            return 1.0;
        }

        return (float) (config('opnam.faktor_ketebalan.'.$input) ?? 1.0);
    }

    public function faktorKompleksitas(mixed $tingkat): float
    {
        if ($tingkat === null || $tingkat === '') {
            return 1.0;
        }

        return (float) (config("opnam.faktor_kompleksitas.{$tingkat}") ?? 1.0);
    }

    /**
     * subtotal_item = volume x unit_price x faktor_ketebalan x faktor_kompleksitas
     */
    public function subtotalItem(
        float|int|string $volume,
        float|int|string $unitPrice,
        float|int|string|null $faktorKetebalan = null,
        float|int|string|null $faktorKompleksitas = null,
    ): float {
        return $this->bulat(
            (float) $volume
            * (float) $unitPrice
            * ($faktorKetebalan === null ? 1.0 : (float) $faktorKetebalan)
            * ($faktorKompleksitas === null ? 1.0 : (float) $faktorKompleksitas),
        );
    }

    /**
     * total_biaya = jumlah subtotal_item semua item (PRD 3.10.4).
     *
     * @param  iterable<mixed>  $items  nilai subtotal, atau array/objek dengan kunci `subtotal`
     */
    public function totalBiaya(iterable $items): float
    {
        $total = 0.0;

        foreach ($items as $item) {
            $subtotal = is_array($item)
                ? ($item['subtotal'] ?? 0)
                : (is_object($item) ? ($item->subtotal ?? 0) : $item);

            $total += (float) $subtotal;
        }

        return $this->bulat($total);
    }

    private function bulat(float $nilai): float
    {
        return round($nilai, (int) config('opnam.pembulatan', 2));
    }
}
