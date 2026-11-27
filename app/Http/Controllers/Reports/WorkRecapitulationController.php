<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Ship;
use App\Models\User;
use App\Services\Reports\RecapitulationExporter;
use App\Services\Reports\RecapitulationQuery;
use App\Support\OpnamStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * AD-16 dan DR-14: melihat dan mengekspor laporan rekapitulasi pekerjaan.
 *
 * Dapat diakses Admin dan Direksi. Petugas tidak berhak (PRD Bagian 2).
 */
class WorkRecapitulationController extends Controller
{
    public function __construct(
        private readonly RecapitulationQuery $query,
        private readonly RecapitulationExporter $exporter,
    ) {}

    public function index(Request $request): View
    {
        $filters = $this->query->normalize($request->query());

        return view('reports.recapitulation.index', [
            'filters' => $filters,
            'rows' => $this->query->builder($filters)->paginate(20)->withQueryString(),
            'summary' => $this->query->summary($filters),
            'statuses' => OpnamStatus::options(),
            'ships' => Ship::withTrashed()
                ->with(['owner' => fn ($query) => $query->withTrashed()])
                ->orderBy('name')
                ->get(),
            'canExport' => $request->user()?->hasRole(User::ROLE_ADMIN, User::ROLE_DIREKSI) ?? false,
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        return $this->exporter->pdf($this->query->normalize($request->query()));
    }

    public function exportExcel(Request $request): Response
    {
        return $this->exporter->excel($this->query->normalize($request->query()));
    }
}
