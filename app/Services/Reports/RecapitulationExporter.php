<?php

namespace App\Services\Reports;

use App\Models\Opnam;
use App\Support\OpnamStatus;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ekspor laporan rekapitulasi ke PDF (AD-16, DR-14) dan Excel (AD-16, DR-14).
 *
 * Ditempatkan di modul laporan Rifqy, bukan sebagai mesin ekspor bersama,
 * karena taskinstruction Bagian 6 no. 8 belum menetapkan pemilik mesin bersama.
 */
class RecapitulationExporter
{
    private const HEADERS = [
        'No',
        'Nomor Opnam',
        'Tanggal',
        'Nama Kapal',
        'Nomor Lambung',
        'Pemilik',
        'Status',
        'Jumlah Item',
        'Total Biaya (Rp)',
    ];

    public function __construct(private readonly RecapitulationQuery $query) {}

    /**
     * @param  array{dari: string, sampai: string, status: string, ship_id: int|null, q: string}  $filters
     */
    public function pdf(array $filters): Response
    {
        $options = new Options;
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('reports.recapitulation.pdf', [
            'rows' => $this->rows($filters),
            'summary' => $this->query->summary($filters),
            'filters' => $filters,
            'generatedAt' => now(),
        ])->render());
        $dompdf->setPaper('a4', 'landscape');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$this->filename($filters, 'pdf').'"',
        ]);
    }

    /**
     * @param  array{dari: string, sampai: string, status: string, ship_id: int|null, q: string}  $filters
     */
    public function excel(array $filters): Response
    {
        $rows = $this->rows($filters);
        $summary = $this->query->summary($filters);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekapitulasi');

        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'REKAPITULASI PEKERJAAN KAPAL');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:I2');
        $sheet->setCellValue('A2', sprintf(
            'Periode %s s.d. %s — dibuat %s',
            $filters['dari'],
            $filters['sampai'],
            now()->format('d/m/Y H:i'),
        ));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->fromArray(self::HEADERS, null, 'A4');
        $sheet->getStyle('A4:I4')->getFont()->setBold(true);
        $sheet->getStyle('A4:I4')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $rowNumber = 5;
        foreach ($rows as $index => $opnam) {
            $sheet->fromArray([
                $index + 1,
                $opnam->opnam_number,
                $opnam->tanggal_opnam?->format('d/m/Y'),
                $opnam->ship?->name,
                $opnam->ship?->hull_number,
                $opnam->ship?->owner?->name,
                OpnamStatus::label($opnam->status),
                $opnam->items_count,
                (float) $opnam->total_biaya,
            ], null, 'A'.$rowNumber);

            $sheet->getStyle('I'.$rowNumber)->getNumberFormat()->setFormatCode('#,##0');
            $rowNumber++;
        }

        $sheet->setCellValue('A'.$rowNumber, 'TOTAL');
        $sheet->mergeCells('A'.$rowNumber.':G'.$rowNumber);
        $sheet->setCellValue('H'.$rowNumber, $summary['total_item']);
        $sheet->setCellValue('I'.$rowNumber, $summary['total_biaya']);
        $sheet->getStyle('A'.$rowNumber.':I'.$rowNumber)->getFont()->setBold(true);
        $sheet->getStyle('I'.$rowNumber)->getNumberFormat()->setFormatCode('#,##0');

        $sheet->getStyle('A4:I'.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->freezePane('A5');

        foreach (['A' => 6, 'B' => 18, 'C' => 12, 'D' => 26, 'E' => 16, 'F' => 26, 'G' => 24, 'H' => 12, 'I' => 18] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $path = tempnam(sys_get_temp_dir(), 'rekap-');
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        return response()
            ->download($path, $this->filename($filters, 'xlsx'), [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend(true);
    }

    /**
     * @param  array{dari: string, sampai: string, status: string, ship_id: int|null, q: string}  $filters
     * @return Collection<int, Opnam>
     */
    private function rows(array $filters): Collection
    {
        return $this->query->builder($filters)->get();
    }

    /**
     * @param  array{dari: string, sampai: string, status: string, ship_id: int|null, q: string}  $filters
     */
    private function filename(array $filters, string $extension): string
    {
        return sprintf('rekap-pekerjaan-%s_sd_%s.%s', $filters['dari'], $filters['sampai'], $extension);
    }
}
