<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\Opnam;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function petugas(): View
    {
        $statuses = [
            Opnam::STATUS_DRAFT,
            Opnam::STATUS_MENUNGGU_REVIEW,
            Opnam::STATUS_PERLU_REVISI,
            Opnam::STATUS_DISETUJUI,
        ];

        $milikSaya = Opnam::query()->where('created_by', auth()->id());

        $counts = (clone $milikSaya)
            ->whereIn('status', $statuses)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $ringkasan = [];
        foreach ($statuses as $status) {
            $ringkasan[$status] = (int) ($counts[$status] ?? 0);
        }

        $terbaru = (clone $milikSaya)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get(['id', 'opnam_number', 'status', 'created_at']);

        return view('dashboard.petugas', compact('ringkasan', 'terbaru'));
    }

    public function admin(): View
    {
        $statuses = [
            Opnam::STATUS_MENUNGGU_REVIEW,
            Opnam::STATUS_PERLU_REVISI,
            Opnam::STATUS_DISETUJUI,
        ];

        $counts = Opnam::query()
            ->whereIn('status', $statuses)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $antrean = [];
        foreach ($statuses as $status) {
            $antrean[$status] = (int) ($counts[$status] ?? 0);
        }

        $penggunaAktif = User::query()->where('is_active', true)->count();
        $penggunaNonaktif = User::query()->where('is_active', false)->count();

        return view('dashboard.admin', compact('antrean', 'penggunaAktif', 'penggunaNonaktif'));
    }

    public function direksi(): View
    {
        $menunggu = Billing::query()
            ->where('status', Billing::STATUS_MENUNGGU_APPROVAL);

        $jumlahMenunggu = (clone $menunggu)->count();
        $totalMenunggu = (clone $menunggu)->sum('total_amount');

        return view('dashboard.direksi', compact('jumlahMenunggu', 'totalMenunggu'));
    }
}
