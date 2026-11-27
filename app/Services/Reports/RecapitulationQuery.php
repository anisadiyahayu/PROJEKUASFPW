<?php

namespace App\Services\Reports;

use App\Models\Opnam;
use App\Models\OpnamItem;
use App\Support\OpnamStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * Query dan ringkasan laporan rekapitulasi pekerjaan (AD-16, DR-14).
 *
 * Tidak ada tabel laporan pada ERD, sehingga laporan dihitung langsung dari
 * OPNAMS (snapshot total_biaya) dan OPNAM_ITEMS. Nilai snapshot tidak pernah
 * dihitung ulang dari tarif master (BR-10).
 */
class RecapitulationQuery
{
    /**
     * Menormalkan filter dari query string menjadi bentuk yang pasti.
     *
     * Periode default adalah bulan berjalan bila pengguna tidak mengisinya.
     *
     * @param  array<string, mixed>  $input
     * @return array{dari: string, sampai: string, status: string, ship_id: int|null, q: string}
     */
    public function normalize(array $input): array
    {
        $dari = $this->date($input['dari'] ?? null) ?? now()->startOfMonth()->toDateString();
        $sampai = $this->date($input['sampai'] ?? null) ?? now()->endOfMonth()->toDateString();

        if ($dari > $sampai) {
            [$dari, $sampai] = [$sampai, $dari];
        }

        $status = $input['status'] ?? '';
        $status = is_string($status) ? $status : '';
        if (! in_array($status, OpnamStatus::all(), true)) {
            $status = '';
        }

        $shipId = $input['ship_id'] ?? null;

        return [
            'dari' => $dari,
            'sampai' => $sampai,
            'status' => $status,
            'ship_id' => is_numeric($shipId) ? (int) $shipId : null,
            'q' => is_string($input['q'] ?? null) ? trim($input['q']) : '',
        ];
    }

    /**
     * @param  array{dari: string, sampai: string, status: string, ship_id: int|null, q: string}  $filters
     */
    public function builder(array $filters): Builder
    {
        return $this->baseBuilder($filters)
            ->with([
                'ship' => fn ($query) => $query->withTrashed(),
                'ship.owner' => fn ($query) => $query->withTrashed(),
                'creator',
            ])
            ->withCount('items');
    }

    /**
     * @param  array{dari: string, sampai: string, status: string, ship_id: int|null, q: string}  $filters
     * @return array{total_opnam: int, total_item: int, total_biaya: float}
     */
    public function summary(array $filters): array
    {
        $base = $this->baseBuilder($filters);

        return [
            'total_opnam' => (clone $base)->count(),
            'total_item' => (int) OpnamItem::query()
                ->whereIn('opnam_id', (clone $base)->select('opnams.id'))
                ->count(),
            'total_biaya' => (float) (clone $base)->sum('total_biaya'),
        ];
    }

    /**
     * @param  array{dari: string, sampai: string, status: string, ship_id: int|null, q: string}  $filters
     */
    private function baseBuilder(array $filters): Builder
    {
        return Opnam::query()
            ->whereBetween('tanggal_opnam', [$filters['dari'], $filters['sampai']])
            ->when($filters['status'] !== '', fn (Builder $query) => $query->where('status', $filters['status']))
            ->when($filters['ship_id'] !== null, fn (Builder $query, int $shipId) => $query->where('ship_id', $shipId))
            ->when($filters['q'] !== '', function (Builder $query) use ($filters) {
                $q = $filters['q'];
                $query->where(function (Builder $builder) use ($q) {
                    $builder->where('opnam_number', 'like', "%{$q}%")
                        ->orWhereHas('ship', fn (Builder $ship) => $ship
                            ->withTrashed()
                            ->where('name', 'like', "%{$q}%")
                            ->orWhere('hull_number', 'like', "%{$q}%"));
                });
            });
    }

    private function date(mixed $value): ?string
    {
        if (! is_string($value) || preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('!Y-m-d', $value);
        } catch (Throwable) {
            return null;
        }

        return $date->format('Y-m-d') === $value ? $value : null;
    }
}
