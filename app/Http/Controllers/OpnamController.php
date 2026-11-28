<?php

namespace App\Http\Controllers;

use App\Models\MaterialSpec;
use App\Models\Opnam;
use App\Models\OpnamAttachment;
use App\Models\OpnamItem;
use App\Models\Ship;
use App\Models\Tariff;
use App\Models\Unit;
use App\Models\User;
use App\Models\WorkCategory;
use App\Models\WorkType;
use App\Services\OpnamCostCalculator;
use App\Services\OpnamNumberGenerator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OpnamController extends Controller
{
    private const SESSION_KEY = 'opnam_draft';

    public function __construct(
        private readonly OpnamCostCalculator $calculator,
        private readonly OpnamNumberGenerator $numberGenerator,
    ) {}

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

        $operator = (string) $request->query('operator', '');
        if (ctype_digit($operator)) {
            $query->where('created_by', (int) $operator);
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

        $operators = User::where('role', 'petugas')->orderBy('name')->get();

        return view('opnams.index', compact('opnams', 'statuses', 'operators'));
    }

    /**
     * AD-05: detail opnam bersifat baca saja. Tidak memuat aksi review/approval
     * karena itu bagian dari modul Hazel (AD-06 dan seterusnya).
     */
    public function show(Opnam $opnam): View
    {
        $opnam->load([
            'ship.owner',
            'creator',
            'items.tariff.unit',
            'items.tariff.materialSpec' => fn ($query) => $query->withTrashed(),
            'items.attachments',
        ]);

        return view('opnams.show', compact('opnam'))
            ->with('backRoute', route('admin.opnams.index'));
    }

    public function showPetugas(Opnam $opnam): View
    {
        if ($opnam->created_by !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke opnam ini.');
        }

        $opnam->load([
            'ship.owner',
            'creator',
            'items.tariff.unit',
            'items.tariff.materialSpec' => fn ($query) => $query->withTrashed(),
            'items.attachments',
        ]);

        return view('opnams.show', compact('opnam'))
            ->with('backRoute', route('petugas.opnams.index'));
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

        return view('opnams.mine', compact('opnams', 'statuses'));
    }

    public function create(Request $request): View
    {
        $step = max(1, min(5, (int) $request->query('step', 1)));
        $draft = session(self::SESSION_KEY, $this->emptyDraft());

        return view('opnams.create', [
            'step' => $step,
            'draft' => $draft,
            'tanggalOpnam' => $draft['tanggal_opnam'] ?? null,
            'ships' => Ship::with('owner')->orderBy('name')->get(),
            'categories' => WorkCategory::orderBy('name')->get(),
            'workTypes' => WorkType::orderBy('name')->get(),
            'materialSpecs' => MaterialSpec::orderBy('name')->get(),
            // Kirim seluruh satuan; pratinjau JS yang menyaring berdasarkan
            // material dan tarif berlaku. Jangan di-unique_by_name karena
            // tarif bisa merujuk ke satuan duplikat (mis. dua baris "m²").
            'units' => Unit::orderBy('id')->get(),
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
                    'catatan' => ['nullable', 'string'],
                ]);
                $draft['ship_id'] = $data['ship_id'];
                $draft['tanggal_opnam'] = $data['tanggal_opnam'];
                $draft['lokasi'] = $data['lokasi'];
                $draft['catatan'] = $data['catatan'] ?? null;
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
                        ->where('effective_from', '<=', $draft['tanggal_opnam'] ?? now()->toDateString())
                        ->orderByDesc('effective_from')
                        ->first();

                    if (! $tariff) {
                        return back()->withErrors(['items' => 'Tarif berlaku untuk kombinasi material dan satuan ini pada tanggal opnam tidak ditemukan.'])->withInput();
                    }

                    $unitPrice = (float) $tariff->price;
                    $volume = (float) $item['volume'];
                    $kompleksitas = $item['kompleksitas'] ?? null;
                    $faktorKetebalan = $this->calculator->faktorKetebalan($item['ketebalan'] ?? null);
                    $faktorKompleksitas = $this->calculator->faktorKompleksitas($kompleksitas);
                    $subtotal = $this->calculator->subtotalItem(
                        $volume,
                        $unitPrice,
                        $faktorKetebalan,
                        $faktorKompleksitas,
                    );
                    $items[] = [
                        'uid' => $item['uid'] ?? uniqid('item-'),
                        'work_category_id' => (int) $item['work_category_id'],
                        'work_type_id' => (int) $item['work_type_id'],
                        'material_spec_id' => (int) $item['material_spec_id'],
                        'unit_id' => (int) $item['unit_id'],
                        'tariff_id' => $tariff->id,
                        'volume' => $volume,
                        'unit_price' => $unitPrice,
                        'faktor' => round($faktorKetebalan * $faktorKompleksitas, 2),
                        'faktor_ketebalan' => $faktorKetebalan,
                        'faktor_kompleksitas' => $faktorKompleksitas,
                        'ketebalan' => $item['ketebalan'] ?? null,
                        'kompleksitas' => $kompleksitas,
                        'catatan_tambahan' => $item['catatan_tambahan'] ?? null,
                        'subtotal' => $subtotal,
                        'adjustment' => 0,
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
                $unggahan = config('opnam.unggahan');
                $request->validate([
                    'bukti' => ['nullable', 'array'],
                    'bukti.*' => ['array'],
                    'bukti.*.*' => ['file', 'mimes:'.$unggahan['mimes'], 'max:'.$unggahan['max_kb']],
                ]);

                $validUids = collect($draft['items'] ?? [])->pluck('uid')->filter()->all();
                $uploads = $draft['uploads'] ?? [];
                foreach ($request->file('bukti', []) as $uid => $files) {
                    if (! in_array($uid, $validUids, true) || ! is_array($files)) {
                        continue;
                    }
                    foreach ($files as $file) {
                        $uploads[$uid][] = [
                            'file_path' => $file->store($unggahan['path'], $unggahan['disk']),
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

    /**
     * PT-03: ubah dan hapus item pada draft tersimpan (PRD 4.6).
     * Status perlu_revisi sengaja tidak disentuh karena PT-09 masih parkir.
     */
    public function edit(Opnam $opnam): View
    {
        $this->pastikanDraftMilikSendiri($opnam);

        return view('opnams.edit', [
            'opnam' => $opnam->load('items'),
            'categories' => WorkCategory::orderBy('name')->get(),
            'workTypes' => WorkType::orderBy('name')->get(),
            'materialSpecs' => MaterialSpec::orderBy('name')->get(),
            'units' => Unit::orderBy('id')->get(),
            'tariffs' => Tariff::orderByDesc('effective_from')->get(),
            'tanggalOpnam' => $opnam->tanggal_opnam,
        ]);
    }

    public function storeItem(Request $request, Opnam $opnam): RedirectResponse
    {
        $this->pastikanDraftMilikSendiri($opnam);

        $data = $this->validasiItem($request);
        if (! $this->kombinasiValid($data)) {
            return back()->withErrors(['items' => 'Kombinasi kategori, jenis, dan material tidak valid.'])->withInput();
        }

        $tariff = $this->cariTarif((int) $data['material_spec_id'], (int) $data['unit_id'], $opnam->tanggal_opnam);
        if (! $tariff) {
            return back()->withErrors(['items' => 'Tarif berlaku untuk kombinasi material dan satuan ini pada tanggal opnam tidak ditemukan.'])->withInput();
        }

        $unitPrice = (float) $tariff->price;
        $faktorKetebalan = $this->calculator->faktorKetebalan($data['ketebalan'] ?? null);
        $faktorKompleksitas = $this->calculator->faktorKompleksitas($data['kompleksitas'] ?? null);

        OpnamItem::create([
            'opnam_id' => $opnam->id,
            'tariff_id' => $tariff->id,
            'volume' => $data['volume'],
            'unit_price' => $unitPrice,
            'subtotal' => $this->calculator->subtotalItem($data['volume'], $unitPrice, $faktorKetebalan, $faktorKompleksitas),
            'faktor' => round($faktorKetebalan * $faktorKompleksitas, 2),
            'faktor_ketebalan' => $faktorKetebalan,
            'faktor_kompleksitas' => $faktorKompleksitas,
            'ketebalan' => $data['ketebalan'] ?? null,
            'kompleksitas' => $data['kompleksitas'] ?? 'low',
            'catatan_tambahan' => $data['catatan_tambahan'] ?? null,
            'description' => $data['description'],
            'sort_order' => ((int) $opnam->items()->max('sort_order')) + 1,
        ]);

        $this->hitungUlangTotal($opnam);

        return redirect()->route('petugas.opnams.edit', $opnam)->with('status', 'Item pekerjaan ditambahkan.');
    }

    public function updateItem(Request $request, Opnam $opnam, OpnamItem $item): RedirectResponse
    {
        $this->pastikanDraftMilikSendiri($opnam);
        abort_unless($item->opnam_id === $opnam->id, 404);

        $data = $this->validasiItem($request);
        if (! $this->kombinasiValid($data)) {
            return back()->withErrors(['items' => 'Kombinasi kategori, jenis, dan material tidak valid.'])->withInput();
        }

        // BR-35: hanya material atau satuan yang diubah yang mencari tarif ulang.
        $tarifSekarang = $item->tariff;
        $gantiTarif = ! $tarifSekarang
            || (int) $data['material_spec_id'] !== (int) $tarifSekarang->material_spec_id
            || (int) $data['unit_id'] !== (int) $tarifSekarang->unit_id;

        if ($gantiTarif) {
            $tariff = $this->cariTarif((int) $data['material_spec_id'], (int) $data['unit_id'], $opnam->tanggal_opnam);
            if (! $tariff) {
                return back()->withErrors(['items' => 'Tarif berlaku untuk kombinasi material dan satuan ini pada tanggal opnam tidak ditemukan.'])->withInput();
            }
            $tariffId = $tariff->id;
            $unitPrice = (float) $tariff->price;
        } else {
            $tariffId = $item->tariff_id;
            $unitPrice = (float) $item->unit_price;
        }

        $faktorKetebalan = $this->calculator->faktorKetebalan($data['ketebalan'] ?? null);
        $faktorKompleksitas = $this->calculator->faktorKompleksitas($data['kompleksitas'] ?? null);

        $item->update([
            'tariff_id' => $tariffId,
            'unit_price' => $unitPrice,
            'volume' => $data['volume'],
            'subtotal' => $this->calculator->subtotalItem($data['volume'], $unitPrice, $faktorKetebalan, $faktorKompleksitas),
            'faktor' => round($faktorKetebalan * $faktorKompleksitas, 2),
            'faktor_ketebalan' => $faktorKetebalan,
            'faktor_kompleksitas' => $faktorKompleksitas,
            'ketebalan' => $data['ketebalan'] ?? null,
            'kompleksitas' => $data['kompleksitas'] ?? 'low',
            'catatan_tambahan' => $data['catatan_tambahan'] ?? null,
            'description' => $data['description'],
        ]);

        $this->hitungUlangTotal($opnam);

        return redirect()->route('petugas.opnams.edit', $opnam)->with('status', 'Item pekerjaan diperbarui.');
    }

    public function destroyItem(Opnam $opnam, OpnamItem $item): RedirectResponse
    {
        $this->pastikanDraftMilikSendiri($opnam);
        abort_unless($item->opnam_id === $opnam->id, 404);

        foreach ($item->attachments as $attachment) {
            Storage::disk(config('opnam.unggahan.disk', 'public'))->delete($attachment->file_path);
            $attachment->delete();
        }
        $item->delete();

        $this->hitungUlangTotal($opnam);

        return redirect()->route('petugas.opnams.edit', $opnam)->with('status', 'Item pekerjaan dihapus.');
    }

    private function validasiItem(Request $request): array
    {
        return $request->validate([
            'work_category_id' => ['required', 'exists:work_categories,id'],
            'work_type_id' => ['required', 'exists:work_types,id'],
            'material_spec_id' => ['required', 'exists:material_specs,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'volume' => ['required', 'numeric', 'min:0'],
            'ketebalan' => ['nullable', 'integer', 'min:0'],
            'kompleksitas' => ['required', 'in:low,medium,high'],
            'catatan_tambahan' => ['nullable', 'string'],
            'description' => ['required', 'string'],
        ]);
    }

    private function kombinasiValid(array $data): bool
    {
        $workType = WorkType::find($data['work_type_id']);
        $material = MaterialSpec::find($data['material_spec_id']);

        return $workType && $material
            && (int) $workType->work_category_id === (int) $data['work_category_id']
            && (int) $material->work_type_id === (int) $data['work_type_id'];
    }

    private function cariTarif(int $materialId, int $unitId, mixed $tanggalOpnam): ?Tariff
    {
        return Tariff::where('material_spec_id', $materialId)
            ->where('unit_id', $unitId)
            ->where('effective_from', '<=', $tanggalOpnam ?? now()->toDateString())
            ->orderByDesc('effective_from')
            ->first();
    }

    private function hitungUlangTotal(Opnam $opnam): void
    {
        $opnam->update([
            'total_biaya' => $this->calculator->totalBiaya($opnam->items()->get()->pluck('subtotal')),
        ]);
    }

    private function pastikanDraftMilikSendiri(Opnam $opnam): void
    {
        abort_unless(
            $opnam->status === Opnam::STATUS_DRAFT && (int) $opnam->created_by === (int) auth()->id(),
            403,
        );
    }

    private function emptyDraft(): array
    {
        return ['ship_id' => null, 'tanggal_opnam' => null, 'lokasi' => null, 'catatan' => null, 'adjustment_amount' => 0, 'items' => [], 'uploads' => []];
    }

    private function saveDraft(array $draft): RedirectResponse
    {
        if (empty($draft['ship_id']) || empty($draft['items'])) {
            return redirect()->route('petugas.opnams.create')
                ->with('status', 'Draft belum lengkap. Silakan ulangi dari awal.');
        }

        $ship = Ship::findOrFail($draft['ship_id']);
        $adjustment = round((float) ($draft['adjustment_amount'] ?? 0), 2);
        $total = round($this->calculator->totalBiaya($draft['items']) + $adjustment, 2);

        $opnam = $this->numberGenerator->create([
            'ship_id' => $draft['ship_id'],
            'created_by' => auth()->id(),
            'status' => Opnam::STATUS_DRAFT,
            'tanggal_opnam' => $draft['tanggal_opnam'],
            'lokasi' => $draft['lokasi'] ?? null,
            'jenis_kapal' => $ship->jenis_kapal,
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
                'faktor' => $item['faktor'] ?? 1,
                'faktor_ketebalan' => $item['faktor_ketebalan'] ?? 1,
                'faktor_kompleksitas' => $item['faktor_kompleksitas'] ?? 1,
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
            ->with('status', "Draft {$opnam->opnam_number} berhasil disimpan.");
    }
}
