<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Billing;
use App\Models\Invoice;
use App\Models\Opnam;
use App\Models\TechnicalReport;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class DokumenTagihanService
{
    private const REVISABLE_FIELDS = [
        'ship_name', 'hull_number', 'owner_name', 'owner_address', 'issue_date',
    ];

    public function terbitkanDraf(Billing $billing, User $issuer): void
    {
        $opnam = $billing->opnam;
        $ship = $opnam?->ship;
        $owner = $ship?->owner;

        if (! $billing->invoice) {
            $invoice = Invoice::create([
                'billing_id' => $billing->getKey(),
                'issued_by' => $issuer->getKey(),
                'invoice_number' => 'INV-DRAFT-'.Str::upper(Str::random(12)),
                'issue_date' => now()->toDateString(),
                'ship_name' => $ship?->name ?? '',
                'hull_number' => $ship?->hull_number ?? '',
                'owner_name' => $owner?->name ?? '',
                'owner_address' => $owner?->address,
                'subtotal_amount' => $billing->subtotal_amount,
                'adjustment_amount' => $billing->adjustment_amount,
                'total_amount' => $billing->total_amount,
                'revision_count' => 0,
                'status' => Invoice::STATUS_DRAFT,
            ]);

            $invoice->update([
                'invoice_number' => sprintf('INV-%06d', $invoice->getKey()),
            ]);
        }

        if (! $billing->technicalReport) {
            $report = TechnicalReport::create([
                'billing_id' => $billing->getKey(),
                'issued_by' => $issuer->getKey(),
                'report_number' => 'TR-DRAFT-'.Str::upper(Str::random(12)),
                'issue_date' => now()->toDateString(),
                'ship_name' => $ship?->name ?? '',
                'hull_number' => $ship?->hull_number ?? '',
                'owner_name' => $owner?->name ?? '',
                'owner_address' => $owner?->address,
            ]);

            $report->update([
                'report_number' => sprintf('TR-%06d', $report->getKey()),
            ]);
        }
    }

    public function publikasikanInvoice(Invoice $invoice, string $event, Request $request): void
    {
        if ($invoice->status === Invoice::STATUS_TERBIT) {
            return;
        }

        DB::transaction(function () use ($invoice, $event, $request) {
            $invoice->update(['status' => Invoice::STATUS_TERBIT]);

            $billing = $invoice->billing;
            $opnam = $billing?->opnam;
            $opnamStatus = $opnam?->status;

            if ($opnam && $opnam->status === Opnam::STATUS_DISETUJUI && $billing->technicalReport) {
                $opnam->update(['status' => Opnam::STATUS_DOKUMEN_TERBIT]);
                $opnam->statusHistories()->create([
                    'from_status' => Opnam::STATUS_DISETUJUI,
                    'to_status' => Opnam::STATUS_DOKUMEN_TERBIT,
                    'note' => 'Invoice dan technical report terbit.',
                    'changed_by' => auth()->id(),
                ]);
                $opnamStatus = Opnam::STATUS_DOKUMEN_TERBIT;
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'event' => $event,
                'subject_type' => $invoice->getMorphClass(),
                'subject_id' => $invoice->getKey(),
                'description' => "Invoice {$invoice->invoice_number} diterbitkan.",
                'old_values' => [
                    'status' => Invoice::STATUS_DRAFT,
                    'opnam_status' => $opnamStatus === Opnam::STATUS_DOKUMEN_TERBIT
                        ? Opnam::STATUS_DISETUJUI
                        : $opnamStatus,
                ],
                'new_values' => [
                    'status' => Invoice::STATUS_TERBIT,
                    'opnam_status' => $opnamStatus,
                ],
                'ip_address' => $request->ip(),
            ]);
        });
    }

    public function revisiInvoice(Invoice $invoice, array $data, Request $request): void
    {
        $old = $invoice->only(self::REVISABLE_FIELDS);

        DB::transaction(function () use ($invoice, $data, $old, $request) {
            $invoice->update(array_merge($data, [
                'revision_count' => $invoice->revision_count + 1,
            ]));

            ActivityLog::create([
                'user_id' => auth()->id(),
                'event' => 'Revisi Invoice',
                'subject_type' => $invoice->getMorphClass(),
                'subject_id' => $invoice->getKey(),
                'description' => "Invoice {$invoice->invoice_number} direvisi.",
                'old_values' => $old,
                'new_values' => $invoice->only(self::REVISABLE_FIELDS)
                    + ['revision_count' => $invoice->revision_count],
                'ip_address' => $request->ip(),
            ]);
        });
    }

    public function unduhInvoice(Invoice $invoice, string $namaFile): Response
    {
        abort_unless(
            $invoice->status === Invoice::STATUS_TERBIT,
            422,
            'Invoice belum terbit.',
        );

        return Pdf::loadView('pdf.invoice', ['invoice' => $invoice])
            ->download($namaFile.'.pdf');
    }

    public function unduhTechnicalReport(TechnicalReport $report, string $namaFile): Response
    {
        return Pdf::loadView('pdf.technical-report', ['report' => $report])
            ->download($namaFile.'.pdf');
    }
}
