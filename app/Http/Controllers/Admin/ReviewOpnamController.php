<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Billing;
use App\Models\Opnam;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewOpnamController extends Controller
{
    private const ROLE = 'admin';

    public function index(Request $request): View
    {
        abort_unless(auth()->user()?->role === self::ROLE, 403);

        $query = Opnam::query()
            ->where('status', Opnam::STATUS_MENUNGGU_REVIEW)
            ->with(['ship', 'creator']);

        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $query->where(function ($builder) use ($q) {
                $builder->where('opnam_number', 'like', "%{$q}%")
                    ->orWhereHas('ship', function ($shipQuery) use ($q) {
                        $shipQuery->where('name', 'like', "%{$q}%")
                            ->orWhere('hull_number', 'like', "%{$q}%");
                    });
            });
        }

        $dari = trim((string) $request->query('tanggal_dari', ''));
        if ($dari !== '') {
            $query->whereDate('tanggal_opnam', '>=', $dari);
        }

        $sampai = trim((string) $request->query('tanggal_sampai', ''));
        if ($sampai !== '') {
            $query->whereDate('tanggal_opnam', '<=', $sampai);
        }

        $opnams = $query->orderByDesc('tanggal_opnam')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.review-opnams.index', compact('opnams'));
    }

    public function show(Opnam $opnam): View
    {
        abort_unless(auth()->user()?->role === self::ROLE, 403);
        abort_unless(
            $opnam->status === Opnam::STATUS_MENUNGGU_REVIEW,
            422,
            'Opnam tidak berada pada status menunggu_review.',
        );

        $opnam->load(['ship', 'creator', 'items', 'attachments']);

        return view('admin.review-opnams.show', compact('opnam'));
    }

    public function approve(Request $request, Opnam $opnam): RedirectResponse
    {
        abort_unless(auth()->user()?->role === self::ROLE, 403);
        abort_unless(
            $opnam->status === Opnam::STATUS_MENUNGGU_REVIEW,
            422,
            'Opnam tidak berada pada status menunggu_review.',
        );

        $from = $opnam->status;
        $to = Opnam::STATUS_MENUNGGU_APPROVAL_DIREKSI;

        DB::transaction(function () use ($request, $opnam, $from, $to) {
            $opnam->update([
                'status' => $to,
                'forwarded_at' => now(),
            ]);

            $opnam->statusHistories()->create([
                'from_status' => $from,
                'to_status' => $to,
                'changed_by' => auth()->id(),
            ]);

            $billing = Billing::firstOrCreate(
                ['opnam_id' => $opnam->getKey()],
                [
                    'billing_number' => null,
                    'status' => Billing::STATUS_MENUNGGU_APPROVAL,
                    'subtotal_amount' => $opnam->total_biaya,
                    'adjustment_amount' => 0,
                    'total_amount' => $opnam->total_biaya,
                ],
            );

            if (! $billing->billing_number) {
                $billing->update(['billing_number' => sprintf('BLG-%06d', $billing->getKey())]);
            }

            if ($billing->wasRecentlyCreated) {
                $billing->statusHistories()->create([
                    'from_status' => null,
                    'to_status' => Billing::STATUS_MENUNGGU_APPROVAL,
                    'note' => 'Dibuat otomatis saat AD-08.',
                    'changed_by' => auth()->id(),
                ]);
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'event' => 'AD-08 Setujui Hasil Review',
                'subject_type' => $opnam->getMorphClass(),
                'subject_id' => $opnam->getKey(),
                'description' => "Opnam {$opnam->opnam_number} diteruskan ke Direksi.",
                'old_values' => ['status' => $from],
                'new_values' => [
                    'status' => $to,
                    'forwarded_at' => $opnam->forwarded_at?->toDateTimeString(),
                    'billing_id' => $billing->getKey(),
                ],
                'ip_address' => $request->ip(),
            ]);
        });

        return back()->with('status', 'Opnam diteruskan ke Direksi.');
    }

    public function returnForRevision(Request $request, Opnam $opnam): RedirectResponse
    {
        abort_unless(auth()->user()?->role === self::ROLE, 403);
        abort_unless(
            $opnam->status === Opnam::STATUS_MENUNGGU_REVIEW,
            422,
            'Opnam tidak berada pada status menunggu_review.',
        );

        $data = $request->validate([
            'catatan' => ['required', 'string'],
        ]);

        $from = $opnam->status;
        $to = Opnam::STATUS_PERLU_REVISI;
        $catatan = $data['catatan'];

        DB::transaction(function () use ($request, $opnam, $from, $to, $catatan) {
            $opnam->update(['status' => $to]);

            $opnam->statusHistories()->create([
                'from_status' => $from,
                'to_status' => $to,
                'note' => $catatan,
                'changed_by' => auth()->id(),
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'event' => 'AD-09 Kembalikan untuk Revisi',
                'subject_type' => $opnam->getMorphClass(),
                'subject_id' => $opnam->getKey(),
                'description' => "Opnam {$opnam->opnam_number} dikembalikan untuk revisi.",
                'old_values' => ['status' => $from],
                'new_values' => ['status' => $to, 'note' => $catatan],
                'ip_address' => $request->ip(),
            ]);
        });

        return back()->with('status', 'Opnam dikembalikan untuk revisi.');
    }
}
