<?php

namespace App\Http\Controllers\Audit;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Audit\ActivityLogPdfExporter;
use App\Services\Audit\ActivityLogQuery;
use App\Support\Audit\ActivitySubject;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * PT-10, AD-14, dan DR-16: riwayat perubahan dan audit log.
 *
 * PRD 9 no. 1 & 2 dikonfirmasi sebagai satu halaman gabungan:
 * - Admin dan Direksi melihat seluruh aktivitas.
 * - Petugas hanya melihat aktivitas miliknya sendiri.
 */
class ActivityLogController extends Controller
{
    private const EVENTS = ActivityLogQuery::EVENTS;

    public function __construct(
        private readonly ActivityLogQuery $query,
        private readonly ActivityLogPdfExporter $exporter,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $isPetugas = $user->hasRole(User::ROLE_PETUGAS);
        $filters = $this->query->filters($request);

        $logs = $this->query->builder($filters, $user)
            ->paginate(25)
            ->withQueryString();

        return view('audit.activity-logs.index', [
            'logs' => $logs,
            'filters' => $filters,
            'events' => self::EVENTS,
            'subjects' => ActivitySubject::options(),
            'users' => $isPetugas ? collect() : User::orderBy('name')->get(),
            'isPetugas' => $isPetugas,
        ]);
    }

    /**
     * PT-11, AD-15, dan DR-18: unduh PDF sesuai filter aktif.
     */
    public function exportPdf(Request $request): Response
    {
        $user = $request->user();
        $filters = $this->query->filters($request);
        $logs = $this->query->builder($filters, $user)->get();

        return $this->exporter->pdf($logs, $filters, $user);
    }
}
