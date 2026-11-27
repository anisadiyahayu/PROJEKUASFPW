<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Opnam;
use App\Models\Ship;
use App\Models\User;
use App\Support\OpnamStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * AD-11 dan DR-15: riwayat perbaikan kapal.
 *
 * Satu halaman dipakai Admin dan Direksi (PRD 9 no. 7).
 * BR-25: pencarian berdasarkan nama kapal atau nomor lambung.
 * DR-15 dibuka dari halaman Transaksi Disetujui, sehingga Direksi hanya
 * melihat opnam berstatus `disetujui` atau `dokumen_terbit`.
 */
class ShipRepairHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->query('q');
        $q = is_string($q) ? trim($q) : '';
        $onlyApproved = $this->onlyApproved();

        $ships = Ship::withTrashed()
            ->with(['owner' => fn ($query) => $query->withTrashed()])
            ->whereHas('opnams', fn (Builder $query) => $this->applyScope($query, $onlyApproved))
            ->withCount(['opnams' => fn (Builder $query) => $this->applyScope($query, $onlyApproved)])
            ->addSelect([
                'last_opnam_date' => Opnam::query()
                    ->select('tanggal_opnam')
                    ->whereColumn('ship_id', 'ships.id')
                    ->when($onlyApproved, fn (Builder $query) => $query->whereIn('status', $this->approvedStatuses()))
                    ->orderByDesc('tanggal_opnam')
                    ->limit(1),
            ])
            ->when($q !== '', function (Builder $query) use ($q) {
                $query->where(function (Builder $builder) use ($q) {
                    $builder->where('name', 'like', "%{$q}%")
                        ->orWhere('hull_number', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('last_opnam_date')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('reports.repair-history.index', [
            'ships' => $ships,
            'q' => $q,
            'onlyApproved' => $onlyApproved,
        ]);
    }

    public function show(Request $request, Ship $ship): View
    {
        $onlyApproved = $this->onlyApproved();
        $statuses = $onlyApproved ? $this->approvedStatuses() : OpnamStatus::all();

        $status = $request->query('status');
        $status = is_string($status) ? $status : '';
        if (! in_array($status, $statuses, true)) {
            $status = '';
        }

        $opnams = $ship->opnams()
            ->with([
                'creator',
                'items.tariff.materialSpec.workType',
                'items.tariff.unit',
            ])
            ->when($onlyApproved, fn (Builder $query) => $query->whereIn('status', $this->approvedStatuses()))
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->orderByDesc('tanggal_opnam')
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'opnam_page')
            ->withQueryString();

        return view('reports.repair-history.show', [
            'ship' => $ship->load(['owner' => fn ($query) => $query->withTrashed()]),
            'opnams' => $opnams,
            'statuses' => $statuses,
            'statusFilter' => $status,
            'onlyApproved' => $onlyApproved,
        ]);
    }

    private function onlyApproved(): bool
    {
        return auth()->user()?->hasRole(User::ROLE_DIREKSI) ?? false;
    }

    /**
     * @return list<string>
     */
    private function approvedStatuses(): array
    {
        return [Opnam::STATUS_DISETUJUI, Opnam::STATUS_DOKUMEN_TERBIT];
    }

    private function applyScope(Builder $query, bool $onlyApproved): void
    {
        if ($onlyApproved) {
            $query->whereIn('status', $this->approvedStatuses());
        }
    }
}
