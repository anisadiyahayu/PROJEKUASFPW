<?php

namespace App\Http\Controllers\Direksi;

use App\Http\Controllers\Controller;
use App\Models\Opnam;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Halaman Transaksi Disetujui untuk Direksi.
 *
 * Menjadi pintu masuk DR-15: Direksi mencari riwayat perbaikan kapal
 * berdasarkan nama kapal atau nomor lambung dari transaksi yang sudah
 * disetujui (BR-25).
 */
class ApprovedTransactionController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->query('q');
        $q = is_string($q) ? trim($q) : '';

        $opnams = Opnam::query()
            ->with([
                'ship' => fn ($query) => $query->withTrashed(),
                'ship.owner' => fn ($query) => $query->withTrashed(),
                'billing',
            ])
            ->withCount('items')
            ->whereIn('status', [Opnam::STATUS_DISETUJUI, Opnam::STATUS_DOKUMEN_TERBIT])
            ->when($q !== '', function (Builder $query) use ($q) {
                $query->where(function (Builder $builder) use ($q) {
                    $builder->where('opnam_number', 'like', "%{$q}%")
                        ->orWhereHas('ship', fn (Builder $ship) => $ship
                            ->where('name', 'like', "%{$q}%")
                            ->orWhere('hull_number', 'like', "%{$q}%"));
                });
            })
            ->orderByDesc('tanggal_opnam')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('direksi.approved.index', compact('opnams', 'q'));
    }
}
