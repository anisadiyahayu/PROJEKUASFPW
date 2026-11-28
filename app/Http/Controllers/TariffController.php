<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\MaterialSpec;
use App\Models\Tariff;
use App\Models\Unit;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TariffController extends Controller
{
    private const UNIQUE_INDEX = 'tariffs_material_spec_unit_effective_from_unique';

    public function index(): View
    {
        $tariffs = Tariff::query()
            ->with([
                'materialSpec' => fn ($query) => $query->withTrashed()->with([
                    'workType' => fn ($workTypes) => $workTypes->withTrashed(),
                ]),
                'unit',
                'creator',
            ])
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->paginate(15);

        return view('tariffs.index', compact('tariffs'));
    }

    public function create(): View
    {
        $materialSpecs = MaterialSpec::query()
            ->with(['workType' => fn ($query) => $query->withTrashed()])
            ->orderBy('name')
            ->get();
        $units = Unit::query()
            ->orderBy('name')
            ->orderBy('code')
            ->get();

        return view('tariffs.create', compact('materialSpecs', 'units'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'material_spec_id' => [
                'required',
                'integer',
                Rule::exists('material_specs', 'id')->whereNull('deleted_at'),
            ],
            'unit_id' => ['required', 'integer', 'exists:units,id'],
            'price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999999.99'],
            'effective_from' => ['required', 'date'],
        ]);

        try {
            DB::transaction(function () use ($data, $request): void {
                $previousTariff = Tariff::query()
                    ->where('material_spec_id', $data['material_spec_id'])
                    ->where('unit_id', $data['unit_id'])
                    ->where('effective_from', '<', $data['effective_from'])
                    ->orderByDesc('effective_from')
                    ->first();

                $tariff = Tariff::create([
                    ...$data,
                    'created_by' => $request->user()->id,
                ]);

                ActivityLog::create([
                    'user_id' => $request->user()->id,
                    'event' => 'Tarif dibuat',
                    'subject_type' => $tariff->getMorphClass(),
                    'subject_id' => $tariff->getKey(),
                    'description' => "Tarif baru untuk material {$tariff->material_spec_id} mulai berlaku {$tariff->effective_from->toDateString()}.",
                    'old_values' => $previousTariff ? [
                        'price' => $previousTariff->price,
                        'effective_from' => $previousTariff->effective_from->toDateString(),
                    ] : null,
                    'new_values' => [
                        'material_spec_id' => $tariff->material_spec_id,
                        'unit_id' => $tariff->unit_id,
                        'price' => $tariff->price,
                        'effective_from' => $tariff->effective_from->toDateString(),
                        'created_by' => $tariff->created_by,
                    ],
                    'ip_address' => $request->ip(),
                ]);
            });
        } catch (QueryException $exception) {
            if (! $this->isDuplicateCombination($exception)) {
                throw $exception;
            }

            return back()
                ->withErrors([
                    'effective_from' => 'Tarif untuk material, satuan, dan tanggal berlaku tersebut sudah ada.',
                ])
                ->withInput();
        }

        return Redirect::route('admin.tariffs.index')
            ->with('status', 'Tarif baru berhasil ditambahkan. Riwayat tarif sebelumnya tidak diubah.');
    }

    public function destroy(Tariff $tariff): RedirectResponse
    {
        try {
            $deleted = DB::transaction(function () use ($tariff): bool {
                $lockedTariff = Tariff::query()
                    ->whereKey($tariff->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedTariff->opnamItems()->exists()) {
                    return false;
                }

                $oldValues = $lockedTariff->only([
                    'material_spec_id',
                    'unit_id',
                    'price',
                    'effective_from',
                    'created_by',
                ]);
                $tariffId = $lockedTariff->getKey();
                $subjectType = $lockedTariff->getMorphClass();

                $lockedTariff->delete();

                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'event' => 'Tarif dihapus',
                    'subject_type' => $subjectType,
                    'subject_id' => $tariffId,
                    'description' => "Tarif {$tariffId} dihapus karena belum digunakan transaksi.",
                    'old_values' => $oldValues,
                    'new_values' => null,
                    'ip_address' => request()->ip(),
                ]);

                return true;
            });
        } catch (QueryException $exception) {
            if (! $this->isForeignKeyViolation($exception)) {
                throw $exception;
            }

            $deleted = false;
        }

        if (! $deleted) {
            return Redirect::route('admin.tariffs.index')
                ->with('error', 'Tarif tidak dapat dihapus karena sudah digunakan oleh transaksi.');
        }

        return Redirect::route('admin.tariffs.index')
            ->with('status', 'Tarif berhasil dihapus.');
    }

    private function isDuplicateCombination(QueryException $exception): bool
    {
        return str_contains($exception->getMessage(), self::UNIQUE_INDEX)
            || str_contains(
                $exception->getMessage(),
                'UNIQUE constraint failed: tariffs.material_spec_id, tariffs.unit_id, tariffs.effective_from',
            );
    }

    private function isForeignKeyViolation(QueryException $exception): bool
    {
        return in_array((string) $exception->getCode(), ['23000', '23503'], true);
    }
}
