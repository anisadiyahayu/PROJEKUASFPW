<?php

namespace App\Http\Controllers;

use App\Models\MaterialSpec;
use App\Models\Opnam;
use App\Models\OpnamAttachment;
use App\Models\OpnamItem;
use App\Models\Ship;
use App\Models\Tariff;
use App\Models\Unit;
use App\Models\WorkCategory;
use App\Models\WorkType;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OpnamController extends Controller
{
    private const SESSION_KEY = 'opnam_draft';

    public function index(Request $request): View
    {
        $statuses = [
            Opnam::STATUS_DRAFT,
            Opnam::STATUS_MENUNGGU_REVIEW,
            Opnam::STATUS_PERLU_REVISI,
            Opnam::STATUS_MENUNGGU_APPROVAL_DIREKSI,
            Opnam::STATUS_DITOLAK,
            Opnam::STATUS_DISETUJUI,
            Opnam::STATUS_DOKUMEN_TERBIT,
        ];

        $query = Opnam::query()->with(['ship', 'creator']);

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

        $status = (string) $request->query('status', '');
        if (in_array($status, $statuses, true)) {
            $query->where('status', $status);
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

        return view('opnams.index', compact('opnams', 'statuses'));
    }

    public function indexMine(Request $request): View
    {
        $statuses = [
            Opnam::STATUS_DRAFT,
            Opnam::STATUS_MENUNGGU_REVIEW,
            Opnam::STATUS_PERLU_REVISI,
            Opnam::STATUS_MENUNGGU_APPROVAL_DIREKSI,
            Opnam::STATUS_DITOLAK,
            Opnam::STATUS_DISETUJUI,
            Opnam::STATUS_DOKUMEN_TERBIT,
        ];

        $query = Opnam::query()->where('created_by', auth()->id())->with('ship');

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

        $status = (string) $request->query('status', '');
        if (in_array($status, $statuses, true)) {
            $query->where('status', $status);
        }

        $tanggal = trim((string) $request->query('tanggal', ''));
        if ($tanggal !== '') {
            $query->whereDate('tanggal_opnam', $tanggal);
        }

        $opnams = $query->orderByDesc('tanggal_opnam')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('opnams.mine', compact('opnams', 'statuses'));
    }

    public function create(Request $request): View
    {
        $step = max(1, min(5, (int) $request->query('step', 1)));

        return view('opnams.create', [
            'step' => $step,
            'draft' => session(self::SESSION_KEY, $this->emptyDraft()),
            'ships' => Ship::with('owner')->orderBy('name')->get(),
            'categories' => WorkCategory::orderBy('name')->get(),
            'workTypes' => WorkType::orderBy('name')->get(),
            'materialSpecs' => MaterialSpec::orderBy('name')->get(),
            'units' => Unit::orderBy('name')->get(),
            'tariffs' => Tariff::orderByDesc('effective_from')->get(),
        ]);
    }

    public function storeStep(Request $request): RedirectResponse
    {
        $step = (int) $request->input('step', 1);
        $draft = session(self::SESSION_KEY, $this->emptyDraft());

        switch ($step) {
            case 1:
                $data = $request->validate([
                    'ship_id' => ['required', 'exists:ships,id'],
                    'tanggal_opnam' => ['required', 'date'],
                    'lokasi' => ['required', 'in:Dock 01 — Graving Dock,Dock 02 — Floating Dock'],
                ]);
                $draft['ship_id'] = $data['ship_id'];
                $draft['tanggal_opnam'] = $data['tanggal_opnam'];
                $draft['lokasi'] = $data['lokasi'];
                $draft['catatan'] = null;
                break;

            case 2:
                $data = $request->validate([
                    'items' => ['required', 'array', 'min:1'],
                    'items.*.work_category_id' => ['required', 'exists:work_categories,id'],
                    'items.*.work_type_id' => ['required', 'exists:work_types,id'],
                    'items.*.material_spec_id' => ['required', 'exists:material_specs,id'],
                    'items.*.unit_id' => ['required', 'exists:units,id'],
                    'items.*.volume' => ['required', 'numeric', 'min:0'],
                    'items.*.ketebalan' => ['nullable', 'integer', 'min:0'],
                    'items.*.kompleksitas' => ['required', 'in:low,medium,high'],
                    'items.*.catatan_tambahan' => ['nullable', 'string'],
                    'items.*.description' => ['required', 'string'],
                    'items.*.uid' => ['nullable', 'string', 'max:100'],
                ]);

                $items = [];
                foreach ($data['items'] as $item) {
                    $workType = WorkType::findOrFail($item['work_type_id']);
                    $material = MaterialSpec::findOrFail($item['material_spec_id']);
                    $unit = Unit::findOrFail($item['unit_id']);

                    if ((int) $workType->work_category_id !== (int) $item['work_category_id']
                        || (int) $material->work_type_id !== (int) $item['work_type_id']) {
                        return back()->withErrors(['items' => 'Kombinasi kategori, jenis, dan material tidak valid.'])->withInput();
                    }

                    $tariff = Tariff::where('material_spec_id', $material->id)
                        ->where('unit_id', $unit->id)
                        ->orderByDesc('effective_from')
                        ->first();

                    if (! $tariff) {
                        return back()->withErrors(['items' => 'Tarif untuk kombinasi material dan satuan ini tidak ditemukan.'])->withInput();
                    }

                    $unitPrice = (float) $tariff->price;
                    $volume = (float) $item['volume'];
                    $kompleksitas = $item['kompleksitas'];
                    $faktor = ['low' => 1, 'medium' => 1.10, 'high' => 1.25][$kompleksitas] ?? 1;
                    $base = round($unitPrice * $volume, 2);
                    $adjustment = round($base * ($faktor - 1), 2);
                    $items[] = [
                        'uid' => $item['uid'] ?? uniqid('item-'),
                        'work_category_id' => (int) $item['work_category_id'],
                        'work_type_id' => (int) $item['work_type_id'],
                        'material_spec_id' => (int) $item['material_spec_id'],
                        'unit_id' => (int) $item['unit_id'],
                        'tariff_id' => $tariff->id,
                        'volume' => $volume,
                        'unit_price' => $unitPrice,
                        'faktor' => $faktor,
                        'ketebalan' => $item['ketebalan'] ?? null,
                        'kompleksitas' => $kompleksitas,
                        'catatan_tambahan' => $item['catatan_tambahan'] ?? null,
                        'subtotal' => $base,
                        'adjustment' => $adjustment,
                        'lokasi' => $item['lokasi'] ?? null,
                        'description' => $item['description'],
                        'work_type_name' => $workType->name,
                        'material_name' => $material->name,
                        'unit_name' => $unit->name,
                    ];
                }
                $draft['items'] = $items;
                break;

            case 3:
                if (empty($draft['items'])) {
                    return redirect()->route('petugas.opnams.create', ['step' => 2]);
                }
                break;

            case 4:
                $request->validate([
                    'bukti' => ['nullable', 'array'],
                    'bukti.*' => ['array'],
                    'bukti.*.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
                ]);

                $validUids = collect($draft['items'] ?? [])->pluck('uid')->filter()->all();
                $uploads = $draft['uploads'] ?? [];
                foreach ($request->file('bukti', []) as $uid => $files) {
                    if (! in_array($uid, $validUids, true) || ! is_array($files)) {
                        continue;
                    }
                    foreach ($files as $file) {
                        $uploads[$uid][] = [
                            'file_path' => $file->store('opnam-attachments', 'public'),
                            'original_name' => $file->getClientOriginalName(),
                            'mime_type' => $file->getClientMimeType(),
                            'file_size' => $file->getSize(),
                        ];
                    }
                }
                $draft['uploads'] = $uploads;
                break;

            case 5:
                return $this->saveDraft($draft);
        }

        session([self::SESSION_KEY => $draft]);

        return redirect()->route('petugas.opnams.create', ['step' => min(5, $step + 1)]);
    }

    private function emptyDraft(): array
    {
        return ['ship_id' => null, 'tanggal_opnam' => null, 'lokasi' => null, 'catatan' => null, 'items' => [], 'uploads' => []];
    }

    private function saveDraft(array $draft): RedirectResponse
    {
        if (empty($draft['ship_id']) || empty($draft['items'])) {
            return redirect()->route('petugas.opnams.create')
                ->with('status', 'Draft belum lengkap. Silakan ulangi dari awal.');
        }

        $year = now()->year;
        $sequence = Opnam::where('opnam_number', 'like', "OPN-{$year}-%")->count() + 1;
        $number = sprintf('OPN-%d-%03d', $year, $sequence);
        $adjustment = round(collect($draft['items'])->sum('adjustment'), 2);
        $total = round(collect($draft['items'])->sum(fn ($item) => $item['subtotal'] + $item['adjustment']), 2);

        $opnam = Opnam::create([
            'opnam_number' => $number,
            'ship_id' => $draft['ship_id'],
            'created_by' => auth()->id(),
            'status' => Opnam::STATUS_DRAFT,
            'tanggal_opnam' => $draft['tanggal_opnam'],
            'lokasi' => $draft['lokasi'] ?? null,
            'adjustment_amount' => $adjustment,
            'catatan' => $draft['catatan'] ?? null,
            'total_biaya' => $total,
        ]);

        $itemIds = [];
        $firstItemId = null;
        foreach ($draft['items'] as $sort => $item) {
            $created = OpnamItem::create([
                'opnam_id' => $opnam->id,
                'tariff_id' => $item['tariff_id'],
                'volume' => $item['volume'],
                'unit_price' => $item['unit_price'],
                'subtotal' => $item['subtotal'],
                'faktor' => $item['faktor'],
                'ketebalan' => $item['ketebalan'] ?? null,
                'kompleksitas' => $item['kompleksitas'] ?? 'low',
                'catatan_tambahan' => $item['catatan_tambahan'] ?? null,
                'description' => $item['description'],
                'sort_order' => $sort + 1,
                'lokasi' => ($item['lokasi'] ?? null) ?: ($draft['lokasi'] ?? null),
            ]);
            if (! empty($item['uid'])) {
                $itemIds[$item['uid']] = $created->id;
            }
            $firstItemId ??= $created->id;
        }

        foreach ($draft['uploads'] ?? [] as $uid => $files) {
            $itemId = $itemIds[$uid] ?? $firstItemId;
            foreach ($files as $upload) {
                OpnamAttachment::create([
                    'opnam_item_id' => $itemId,
                    'uploaded_by' => auth()->id(),
                    'file_path' => $upload['file_path'],
                    'original_name' => $upload['original_name'],
                    'mime_type' => $upload['mime_type'],
                    'file_size' => $upload['file_size'],
                    'keterangan' => null,
                ]);
            }
        }

        session()->forget(self::SESSION_KEY);

        return redirect()->route('petugas.opnams.create')
            ->with('status', "Draft {$number} berhasil disimpan.");
    }
}
