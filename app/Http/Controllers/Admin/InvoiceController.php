<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\DokumenTagihanService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    private const ROLE = 'admin';

    public function edit(Invoice $invoice): View
    {
        $this->authorizeRevision($invoice);

        return view('admin.invoices.edit', compact('invoice'));
    }

    public function update(
        Request $request,
        Invoice $invoice,
        DokumenTagihanService $dokumen,
    ): RedirectResponse {
        $this->authorizeRevision($invoice);

        $dokumen->revisiInvoice($invoice, $this->validateRevision($request), $request);

        return back()->with('status', 'Invoice direvisi.');
    }

    public function download(
        Request $request,
        Invoice $invoice,
        DokumenTagihanService $dokumen,
    ): Response {
        abort_unless(auth()->user()?->role === self::ROLE, 403);

        $dokumen->publikasikanInvoice($invoice, 'AD-13 Ekspor Invoice ke PDF', $request);

        return $dokumen->unduhInvoice($invoice, $invoice->invoice_number);
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
