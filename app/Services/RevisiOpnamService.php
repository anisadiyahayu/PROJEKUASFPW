<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Billing;
use App\Models\BillingAdjustment;
use App\Models\MaterialSpec;
use App\Models\Opnam;
use App\Models\OpnamItem;
use App\Models\Tariff;
use App\Models\Unit;
use App\Models\WorkCategory;
use App\Models\WorkType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * AD-07 dan DR-03: revisi data opnam oleh Admin dan Direksi.
 *
 * Service ini memakai OpnamCostCalculator yang sama dengan form pencatatan,
 * sehingga subtotal dan total tidak berbeda antar jalur (BR-32, BR-35).
 */
final class RevisiOpnamService
{
    public function __construct(private readonly OpnamCostCalculator $calculator) {}

    /**
     * @return array<string, list<string>>
     */
    public function aturanOpnam(): array
    {
        return [
            'tanggal_opnam' => ['required', 'date'],
            'catatan' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public function aturanItem(): array
    {
        return [
            'work_category_id' => ['required', 'exists:work_categories,id'],
            'work_type_id' => ['required', 'exists:work_types,id'],
            'material_spec_id' => ['required', 'exists:material_specs,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'volume' => ['required', 'numeric', 'min:0'],
            'ketebalan' => ['nullable', 'integer', 'min:0'],
            'kompleksitas' => ['required', 'in:low,medium,high'],
            'catatan_tambahan' => ['nullable', 'string'],
            'description' => ['required', 'string'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function kombinasiValid(array $data): bool
    {
        $workType = WorkType::find($data['work_type_id']);
        $material = MaterialSpec::find($data['material_spec_id']);

        return $workType && $material
            && (int) $workType->work_category_id === (int) $data['work_category_id']
            && (int) $material->work_type_id === (int) $data['work_type_id'];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function tarifUntuk(Opnam $opnam, array $data): ?Tariff
    {
        return Tariff::where('material_spec_id', $data['material_spec_id'])
            ->where('unit_id', $data['unit_id'])
            ->where('effective_from', '<=', $opnam->tanggal_opnam ?? now()->toDateString())
            ->orderByDesc('effective_from')
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    public function dataForm(Opnam $opnam, string $mode, array $urls): array
    {
        return [
            'opnam' => $opnam->load([
                'ship',
                'items.tariff.unit',
                'items.tariff.materialSpec' => fn ($query) => $query->withTrashed(),
            ]),
            'mode' => $mode,
            'urls' => $urls,
            'categories' => WorkCategory::orderBy('name')->get(),
            'workTypes' => WorkType::orderBy('name')->get(),
            'materialSpecs' => MaterialSpec::orderBy('name')->get(),
            'units' => Unit::orderBy('id')->get(),
            'tariffs' => Tariff::orderByDesc('effective_from')->get(),
            'tanggalOpnam' => $opnam->tanggal_opnam,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function perbaruiOpnam(Opnam $opnam, array $data, string $event, Request $request): void
    {
        $old = $this->opnamSnapshot($opnam);

        DB::transaction(function () use ($opnam, $data, $event, $request, $old) {
            $opnam->update([
                'tanggal_opnam' => $data['tanggal_opnam'],
                'catatan' => $data['catatan'] ?? null,
            ]);

            $this->hitungUlang($opnam);
            $this->catat($opnam, $event, 'data opnam diperbarui', $old, $this->opnamSnapshot($opnam), $request);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function tambahItem(Opnam $opnam, array $data, string $event, Request $request): void
    {
        if (! $this->kombinasiValid($data)) {
            throw ValidationException::withMessages(['items' => 'Kombinasi kategori, jenis, dan material tidak valid.']);
        }

        $tariff = $this->tarifUntuk($opnam, $data);
        if (! $tariff) {
            throw ValidationException::withMessages(['items' => 'Tarif berlaku untuk kombinasi material dan satuan ini pada tanggal opnam tidak ditemukan.']);
        }

        $payload = $this->payloadItem($data, $tariff);

        DB::transaction(function () use ($opnam, $payload, $event, $request) {
            $item = OpnamItem::create($payload + [
                'opnam_id' => $opnam->getKey(),
                'lokasi' => $opnam->lokasi,
                'sort_order' => ((int) $opnam->items()->max('sort_order')) + 1,
            ]);

            $this->hitungUlang($opnam);
            $this->catat($opnam, $event, 'item pekerjaan ditambahkan', ['item' => null], ['item' => $this->itemSnapshot($item)], $request);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function perbaruiItem(Opnam $opnam, OpnamItem $item, array $data, string $event, Request $request): void
    {
        abort_unless($item->opnam_id === $opnam->getKey(), 404);

        if (! $this->kombinasiValid($data)) {
            throw ValidationException::withMessages(['items' => 'Kombinasi kategori, jenis, dan material tidak valid.']);
        }

        // BR-35: harga satuan hanya dicari ulang bila material atau satuan berubah.
        $tarifSekarang = $item->tariff;
        $gantiTarif = ! $tarifSekarang
            || (int) $data['material_spec_id'] !== (int) $tarifSekarang->material_spec_id
            || (int) $data['unit_id'] !== (int) $tarifSekarang->unit_id;

        if ($gantiTarif) {
            $tariff = $this->tarifUntuk($opnam, $data);
            if (! $tariff) {
                throw ValidationException::withMessages(['items' => 'Tarif berlaku untuk kombinasi material dan satuan ini pada tanggal opnam tidak ditemukan.']);
            }
            $payload = $this->payloadItem($data, $tariff);
        } else {
            $faktorKetebalan = $this->calculator->faktorKetebalan($data['ketebalan'] ?? null);
            $faktorKompleksitas = $this->calculator->faktorKompleksitas($data['kompleksitas'] ?? null);
            $payload = [
                'tariff_id' => $item->tariff_id,
                'volume' => $data['volume'],
                'unit_price' => (float) $item->unit_price,
                'subtotal' => $this->calculator->subtotalItem($data['volume'], $item->unit_price, $faktorKetebalan, $faktorKompleksitas),
                'faktor' => round($faktorKetebalan * $faktorKompleksitas, 2),
                'faktor_ketebalan' => $faktorKetebalan,
                'faktor_kompleksitas' => $faktorKompleksitas,
                'ketebalan' => $data['ketebalan'] ?? null,
                'kompleksitas' => $data['kompleksitas'] ?? 'low',
                'catatan_tambahan' => $data['catatan_tambahan'] ?? null,
                'description' => $data['description'],
            ];
        }

        $old = $this->itemSnapshot($item);

        DB::transaction(function () use ($opnam, $item, $payload, $event, $request, $old) {
            $item->update($payload);
            $this->hitungUlang($opnam);
            $this->catat($opnam, $event, 'item pekerjaan diperbarui', ['item' => $old], ['item' => $this->itemSnapshot($item)], $request);
        });
    }

    public function hapusItem(Opnam $opnam, OpnamItem $item, string $event, Request $request): void
    {
        abort_unless($item->opnam_id === $opnam->getKey(), 404);

        $old = $this->itemSnapshot($item);

        DB::transaction(function () use ($opnam, $item, $event, $request, $old) {
            foreach ($item->attachments as $attachment) {
                Storage::disk(config('opnam.unggahan.disk', 'public'))->delete($attachment->file_path);
                $attachment->delete();
            }
            $item->delete();

            $this->hitungUlang($opnam);
            $this->catat($opnam, $event, 'item pekerjaan dihapus', ['item' => $old], ['item' => null], $request);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function payloadItem(array $data, Tariff $tariff): array
    {
        $faktorKetebalan = $this->calculator->faktorKetebalan($data['ketebalan'] ?? null);
        $faktorKompleksitas = $this->calculator->faktorKompleksitas($data['kompleksitas'] ?? null);

        return [
            'tariff_id' => $tariff->getKey(),
            'volume' => $data['volume'],
            'unit_price' => (float) $tariff->price,
            'subtotal' => $this->calculator->subtotalItem($data['volume'], $tariff->price, $faktorKetebalan, $faktorKompleksitas),
            'faktor' => round($faktorKetebalan * $faktorKompleksitas, 2),
            'faktor_ketebalan' => $faktorKetebalan,
            'faktor_kompleksitas' => $faktorKompleksitas,
            'ketebalan' => $data['ketebalan'] ?? null,
            'kompleksitas' => $data['kompleksitas'] ?? 'low',
            'catatan_tambahan' => $data['catatan_tambahan'] ?? null,
            'description' => $data['description'],
        ];
    }

    /**
     * Total opnam dihitung ulang dari item yang tersimpan dan penyesuaian yang
     * sudah ada. Tagihan menunggu approval ikut diselaraskan, dengan tanda
     * diskon/markup tetap dipertahankan (BR-19, BR-20, BR-32).
     */
    private function hitungUlang(Opnam $opnam): void
    {
        $total = round(
            $this->calculator->totalBiaya($opnam->items()->get()->pluck('subtotal'))
            + (float) ($opnam->adjustment_amount ?? 0),
            2,
        );

        $opnam->update(['total_biaya' => $total]);

        $billing = Billing::where('opnam_id', $opnam->getKey())->first();
        if (! $billing || $billing->status !== Billing::STATUS_MENUNGGU_APPROVAL) {
            return;
        }

        $penyesuaian = (float) $billing->adjustment_amount;
        $jenis = $billing->adjustments->first()?->adjustment_type;
        $totalTagihan = $jenis === BillingAdjustment::TYPE_DISKON
            ? $total - $penyesuaian
            : $total + $penyesuaian;

        $billing->update([
            'subtotal_amount' => $total,
            'total_amount' => round($totalTagihan, 2),
        ]);
    }

    /**
     * @param  array<string, mixed>  $old
     * @param  array<string, mixed>  $new
     */
    private function catat(Opnam $opnam, string $event, string $keterangan, array $old, array $new, Request $request): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'event' => $event,
            'subject_type' => $opnam->getMorphClass(),
            'subject_id' => $opnam->getKey(),
            'description' => "Opnam {$opnam->opnam_number}: {$keterangan}.",
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => $request->ip(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function opnamSnapshot(Opnam $opnam): array
    {
        return [
            'tanggal_opnam' => $opnam->tanggal_opnam?->toDateString(),
            'catatan' => $opnam->catatan,
            'total_biaya' => (float) $opnam->total_biaya,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function itemSnapshot(OpnamItem $item): array
    {
        return [
            'id' => $item->getKey(),
            'description' => $item->description,
            'tariff_id' => $item->tariff_id,
            'volume' => (float) $item->volume,
            'unit_price' => (float) $item->unit_price,
            'faktor' => (float) $item->faktor,
            'ketebalan' => $item->ketebalan,
            'kompleksitas' => $item->kompleksitas,
            'catatan_tambahan' => $item->catatan_tambahan,
            'subtotal' => (float) $item->subtotal,
        ];
    }
}
