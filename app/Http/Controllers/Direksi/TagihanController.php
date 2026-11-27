<?php

namespace App\Http\Controllers\Direksi;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Billing;
use App\Models\BillingAdjustment;
use App\Models\Opnam;
use App\Models\OpnamItem;
use App\Services\DokumenTagihanService;
use App\Services\RevisiOpnamService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TagihanController extends Controller
{
    private const ROLE = 'direksi';

    private const EVENT_REVISI = 'DR-03 Revisi Data Opnam';

    public function index(Request $request): View
    {
        abort_unless(auth()->user()?->role === self::ROLE, 403);

        $query = Billing::query()
            ->where('status', Billing::STATUS_MENUNGGU_APPROVAL)
            ->with(['opnam.ship']);

        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $query->where(function ($builder) use ($q) {
                $builder->where('billing_number', 'like', "%{$q}%")
                    ->orWhereHas('opnam', function ($opnamQuery) use ($q) {
                        $opnamQuery->where('opnam_number', 'like', "%{$q}%")
                            ->orWhereHas('ship', function ($shipQuery) use ($q) {
                                $shipQuery->where('name', 'like', "%{$q}%")
                                    ->orWhere('hull_number', 'like', "%{$q}%");
                            });
                    });
            });
        }

        $dari = trim((string) $request->query('tanggal_dari', ''));
        if ($dari !== '') {
            $query->whereHas('opnam', function ($opnamQuery) use ($dari) {
                $opnamQuery->whereDate('tanggal_opnam', '>=', $dari);
            });
        }

        $sampai = trim((string) $request->query('tanggal_sampai', ''));
        if ($sampai !== '') {
            $query->whereHas('opnam', function ($opnamQuery) use ($sampai) {
                $opnamQuery->whereDate('tanggal_opnam', '<=', $sampai);
            });
        }

        $billings = $query->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('direksi.tagihans.index', compact('billings'));
    }

    public function show(Billing $billing): View
    {
        abort_unless(auth()->user()?->role === self::ROLE, 403);
        abort_unless(
            $billing->status === Billing::STATUS_MENUNGGU_APPROVAL,
            422,
            'Tagihan tidak berada pada status menunggu_approval.',
        );

        $billing->load([
            'opnam.ship',
            'opnam.items',
            'adjustments.creator',
        ]);

        return view('direksi.tagihans.show', compact('billing'));
    }

    public function revisiOpnam(Billing $billing, RevisiOpnamService $service): View
    {
        $this->authorizeDecision($billing);
        $opnam = $billing->opnam;

        return view('opnams.revisi', $service->dataForm($opnam, 'Direksi', [
            'kembali' => route('direksi.tagihans.show', $billing),
            'simpan_opnam' => route('direksi.tagihans.update-revisi-opnam', $billing),
            'tambah_item' => route('direksi.tagihans.revisi-opnam.items.store', $billing),
            'item_update' => fn (OpnamItem $item) => route('direksi.tagihans.revisi-opnam.items.update', [$billing, $item]),
            'item_destroy' => fn (OpnamItem $item) => route('direksi.tagihans.revisi-opnam.items.destroy', [$billing, $item]),
        ]));
    }

    public function updateRevisiOpnam(Request $request, Billing $billing, RevisiOpnamService $service): RedirectResponse
    {
        $this->authorizeDecision($billing);

        $service->perbaruiOpnam(
            $billing->opnam,
            $request->validate($service->aturanOpnam()),
            self::EVENT_REVISI,
            $request,
        );

        return back()->with('status', 'Data opnam direvisi.');
    }

    public function storeItemRevisi(Request $request, Billing $billing, RevisiOpnamService $service): RedirectResponse
    {
        $this->authorizeDecision($billing);

        $service->tambahItem(
            $billing->opnam,
            $request->validate($service->aturanItem()),
            self::EVENT_REVISI,
            $request,
        );

        return back()->with('status', 'Item pekerjaan ditambahkan.');
    }

    public function updateItemRevisi(Request $request, Billing $billing, OpnamItem $item, RevisiOpnamService $service): RedirectResponse
    {
        $this->authorizeDecision($billing);

        $service->perbaruiItem(
            $billing->opnam,
            $item,
            $request->validate($service->aturanItem()),
            self::EVENT_REVISI,
            $request,
        );

        return back()->with('status', 'Item pekerjaan diperbarui.');
    }

    public function destroyItemRevisi(Request $request, Billing $billing, OpnamItem $item, RevisiOpnamService $service): RedirectResponse
    {
        $this->authorizeDecision($billing);

        $service->hapusItem($billing->opnam, $item, self::EVENT_REVISI, $request);

        return back()->with('status', 'Item pekerjaan dihapus.');
    }

    public function applyAdjustment(Request $request, Billing $billing): RedirectResponse
    {
        $this->authorizeDecision($billing);

        $data = $request->validate([
            'adjustment_type' => [
                'required',
                Rule::in([BillingAdjustment::TYPE_DISKON, BillingAdjustment::TYPE_MARKUP]),
            ],
            'adjustment_value' => ['required', 'numeric', 'min:0'],
            'disposition_note' => ['required', 'string'],
        ]);

        $type = $data['adjustment_type'];
        $amount = (float) $data['adjustment_value'];
        $subtotal = (float) $billing->subtotal_amount;
        $total = $type === BillingAdjustment::TYPE_DISKON
            ? $subtotal - $amount
            : $subtotal + $amount;

        $old = [
            'adjustment_type' => $billing->adjustments->first()?->adjustment_type,
            'adjustment_amount' => $billing->adjustment_amount,
            'total_amount' => $billing->total_amount,
        ];

        DB::transaction(function () use ($request, $billing, $data, $type, $amount, $total, $old) {
            $billing->adjustments()->updateOrCreate([], [
                'created_by' => auth()->id(),
                'adjustment_type' => $type,
                'adjustment_value' => $amount,
                'adjustment_amount' => $amount,
                'disposition_note' => $data['disposition_note'],
            ]);

            $billing->update([
                'adjustment_amount' => $amount,
                'total_amount' => $total,
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'event' => 'DR-04 Diskon atau Markup',
                'subject_type' => $billing->getMorphClass(),
                'subject_id' => $billing->getKey(),
                'description' => "Penyesuaian {$type} dicatat pada tagihan {$billing->billing_number}.",
                'old_values' => $old,
                'new_values' => [
                    'adjustment_type' => $type,
                    'adjustment_value' => $amount,
                    'adjustment_amount' => $amount,
                    'total_amount' => $total,
                    'disposition_note' => $data['disposition_note'],
                ],
                'ip_address' => $request->ip(),
            ]);
        });

        return back()->with('status', 'Diskon atau markup tersimpan.');
    }

    public function approve(Request $request, Billing $billing, DokumenTagihanService $dokumen): RedirectResponse
    {
        $this->authorizeDecision($billing);

        $opnam = $billing->opnam;
        $billingFrom = $billing->status;
        $opnamFrom = $opnam->status;

        DB::transaction(function () use ($request, $billing, $opnam, $billingFrom, $opnamFrom, $dokumen) {
            $billing->update([
                'status' => Billing::STATUS_DISETUJUI,
                'decided_by' => auth()->id(),
                'decided_at' => now(),
            ]);

            $billing->statusHistories()->create([
                'from_status' => $billingFrom,
                'to_status' => Billing::STATUS_DISETUJUI,
                'changed_by' => auth()->id(),
            ]);

            $opnam->update(['status' => Opnam::STATUS_DISETUJUI]);

            $opnam->statusHistories()->create([
                'from_status' => $opnamFrom,
                'to_status' => Opnam::STATUS_DISETUJUI,
                'changed_by' => auth()->id(),
            ]);

            $dokumen->terbitkanDraf($billing, auth()->user());

            ActivityLog::create([
                'user_id' => auth()->id(),
                'event' => 'DR-06 Setujui Tagihan',
                'subject_type' => $billing->getMorphClass(),
                'subject_id' => $billing->getKey(),
                'description' => "Tagihan {$billing->billing_number} disetujui.",
                'old_values' => ['billing_status' => $billingFrom, 'opnam_status' => $opnamFrom],
                'new_values' => [
                    'billing_status' => Billing::STATUS_DISETUJUI,
                    'opnam_status' => Opnam::STATUS_DISETUJUI,
                    'decided_by' => auth()->id(),
                    'decided_at' => $billing->decided_at?->toDateTimeString(),
                ],
                'ip_address' => $request->ip(),
            ]);
        });

        return back()->with('status', 'Tagihan disetujui; invoice dan technical report dibuat.');
    }

    public function reject(Request $request, Billing $billing): RedirectResponse
    {
        $this->authorizeDecision($billing);

        $data = $request->validate([
            'alasan' => ['required', 'string'],
        ]);

        $opnam = $billing->opnam;
        $billingFrom = $billing->status;
        $opnamFrom = $opnam->status;
        $alasan = $data['alasan'];

        DB::transaction(function () use ($request, $billing, $opnam, $billingFrom, $opnamFrom, $alasan) {
            $billing->update([
                'status' => Billing::STATUS_DITOLAK,
                'decided_by' => auth()->id(),
                'decided_at' => now(),
            ]);

            $billing->statusHistories()->create([
                'from_status' => $billingFrom,
                'to_status' => Billing::STATUS_DITOLAK,
                'note' => $alasan,
                'changed_by' => auth()->id(),
            ]);

            $opnam->update(['status' => Opnam::STATUS_DITOLAK]);

            $opnam->statusHistories()->create([
                'from_status' => $opnamFrom,
                'to_status' => Opnam::STATUS_DITOLAK,
                'note' => $alasan,
                'changed_by' => auth()->id(),
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'event' => 'DR-07 Tolak Tagihan',
                'subject_type' => $billing->getMorphClass(),
                'subject_id' => $billing->getKey(),
                'description' => "Tagihan {$billing->billing_number} ditolak.",
                'old_values' => ['billing_status' => $billingFrom, 'opnam_status' => $opnamFrom],
                'new_values' => [
                    'billing_status' => Billing::STATUS_DITOLAK,
                    'opnam_status' => Opnam::STATUS_DITOLAK,
                    'alasan' => $alasan,
                ],
                'ip_address' => $request->ip(),
            ]);
        });

        return back()->with('status', 'Tagihan ditolak.');
    }

    private function authorizeDecision(Billing $billing): void
    {
        abort_unless(auth()->user()?->role === self::ROLE, 403);
        abort_unless(
            $billing->status === Billing::STATUS_MENUNGGU_APPROVAL,
            422,
            'Tagihan tidak berada pada status menunggu_approval.',
        );
        abort_unless(
            $billing->opnam->status === Opnam::STATUS_MENUNGGU_APPROVAL_DIREKSI,
            422,
            'Opnam tidak berada pada status menunggu_approval_direksi.',
        );
    }
}
