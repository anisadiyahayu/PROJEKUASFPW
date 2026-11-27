<?php

namespace App\Http\Controllers\Direksi;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\TechnicalReport;
use App\Services\DokumenTagihanService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class DokumenController extends Controller
{
    private const ROLE = 'direksi';

    public function index(): View
    {
        abort_unless(auth()->user()?->role === self::ROLE, 403);

        $invoices = Invoice::query()
            ->with(['billing.opnam.ship'])
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'invoice_page');
        $reports = TechnicalReport::query()
            ->with(['billing.opnam.ship'])
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'report_page');

        return view('direksi.dokumen.index', compact('invoices', 'reports'));
    }

    public function editInvoice(Invoice $invoice): View
    {
        $this->authorizeRevision($invoice);

        return view('direksi.dokumen.edit-invoice', compact('invoice'));
    }

    public function updateInvoice(
        Request $request,
        Invoice $invoice,
        DokumenTagihanService $dokumen,
    ): RedirectResponse {
        $this->authorizeRevision($invoice);

        $dokumen->revisiInvoice($invoice, $this->validateRevision($request), $request);

        return back()->with('status', 'Invoice direvisi.');
    }

    public function downloadInvoice(
        Request $request,
        Invoice $invoice,
        DokumenTagihanService $dokumen,
    ): Response {
        abort_unless(auth()->user()?->role === self::ROLE, 403);

        $dokumen->publikasikanInvoice($invoice, 'DR-11 Ekspor Invoice ke PDF', $request);

        return $dokumen->unduhInvoice($invoice, $invoice->invoice_number);
    }

    public function unduh(Request $request, DokumenTagihanService $dokumen): Response
    {
        abort_unless(auth()->user()?->role === self::ROLE, 403);

        $data = $request->validate([
            'jenis' => ['required', Rule::in(['invoice', 'technical_report'])],
            'id' => ['required', 'integer'],
        ]);

        if ($data['jenis'] === 'invoice') {
            $invoice = Invoice::findOrFail($data['id']);
            $dokumen->publikasikanInvoice($invoice, 'DR-12 Unduh Dokumen Terbit', $request);

            return $dokumen->unduhInvoice($invoice, $invoice->invoice_number);
        }

        $report = TechnicalReport::findOrFail($data['id']);

        return $dokumen->unduhTechnicalReport($report, $report->report_number);
    }

    private function authorizeRevision(Invoice $invoice): void
    {
        abort_unless(auth()->user()?->role === self::ROLE, 403);
        abort_unless(
            $invoice->status === Invoice::STATUS_DRAFT,
            422,
            'Invoice sudah terbit dan tidak dapat direvisi.',
        );
    }

    private function validateRevision(Request $request): array
    {
        return $request->validate([
            'ship_name' => ['required', 'string'],
            'hull_number' => ['required', 'string'],
            'owner_name' => ['required', 'string'],
            'owner_address' => ['nullable', 'string'],
            'issue_date' => ['required', 'date'],
        ]);
    }
}
