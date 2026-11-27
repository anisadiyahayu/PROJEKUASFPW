<?php

namespace App\Services\Audit;

use App\Models\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\Response;

/**
 * PT-11, AD-15, dan DR-18: ekspor riwayat aktivitas ke PDF.
 */
final class ActivityLogPdfExporter
{
    /**
     * @param  Collection<int, \App\Models\ActivityLog>  $logs
     * @param  array{q: string, event: string, subject_type: string, user_id: int|null, dari: string|null, sampai: string|null}  $filters
     */
    public function pdf(Collection $logs, array $filters, User $user): Response
    {
        $options = new Options;
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($this->html($logs, $filters, $user));
        $dompdf->setPaper('a4', 'landscape');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$this->filename($filters).'"',
        ]);
    }

    /**
     * @param  Collection<int, \App\Models\ActivityLog>  $logs
     * @param  array{q: string, event: string, subject_type: string, user_id: int|null, dari: string|null, sampai: string|null}  $filters
     */
    public function html(Collection $logs, array $filters, User $user): string
    {
        return view('audit.activity-logs.pdf', [
            'logs' => $logs,
            'filters' => $filters,
            'filterUser' => $filters['user_id'] !== null ? User::find($filters['user_id']) : null,
            'user' => $user,
            'eventLabels' => [
                'created' => 'Dibuat',
                'updated' => 'Diperbarui',
                'deleted' => 'Dihapus',
                'restored' => 'Dipulihkan',
            ],
            'generatedAt' => now(),
        ])->render();
    }

    /**
     * @param  array{q: string, event: string, subject_type: string, user_id: int|null, dari: string|null, sampai: string|null}  $filters
     */
    private function filename(array $filters): string
    {
        $dari = $filters['dari'] ?? 'semua';
        $sampai = $filters['sampai'] ?? 'semua';

        return sprintf('riwayat-aktivitas-%s-%s.pdf', $dari, $sampai);
    }
}
