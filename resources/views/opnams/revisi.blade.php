@extends('layouts.simship')

@section('title', 'Revisi Data Opnam')
@section('crumb', 'Revisi Data Opnam')

@section('content')
    @php
        $rupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
    @endphp

    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:16px; flex-wrap:wrap;">
        <div>
            <h1>Revisi {{ $opnam->opnam_number }}</h1>
            <p class="subtitle">Revisi oleh {{ $mode }} &nbsp;•&nbsp; {{ $opnam->ship?->name ?? '-' }} &nbsp;•&nbsp; Estimasi {{ $rupiah($opnam->total_biaya) }}</p>
        </div>
        <a href="{{ $urls['kembali'] }}" class="btn btn-light">Kembali</a>
    </div>

    @if (session('status'))
        <div class="card" style="border-color:#bbf7d0; background:#f0fdf4; color:#166534;">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="card" style="border-color:#fecaca; background:#fef2f2; color:#991b1b;">
            @foreach ($errors->all() as $error) <div>{{ $error }}</div> @endforeach
        </div>
    @endif

    <div class="card">
        <h3 style="margin:0 0 12px;">Informasi Opnam</h3>
        <form method="POST" action="{{ $urls['simpan_opnam'] }}" class="stack">
            @csrf
            @method('PUT')
            <div class="two-col">
                <div>
                    <label for="tanggal_opnam">Tanggal Opname *</label>
                    <input id="tanggal_opnam" type="date" name="tanggal_opnam" value="{{ old('tanggal_opnam', $opnam->tanggal_opnam?->toDateString()) }}" required>
                    @error('tanggal_opnam') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label>Jenis Kapal</label>
                    <div>{{ $opnam->jenis_kapal ?: '-' }}</div>
                </div>
            </div>
            <div>
                <label for="catatan">Catatan Opnam</label>
                <textarea id="catatan" name="catatan" rows="3">{{ old('catatan', $opnam->catatan) }}</textarea>
                @error('catatan') <div class="error">{{ $message }}</div> @enderror
            </div>
            <div><button type="submit" class="btn btn-primary">Simpan Data Opnam</button></div>
        </form>
    </div>

    <div class="card">
        <h3 style="margin:0 0 4px;">Detail Pekerjaan</h3>
        <p class="muted" style="margin:0 0 16px;">Harga satuan hanya dicari ulang bila material atau satuan diubah. Subtotal dan total dihitung ulang memakai kalkulator yang sama dengan form pencatatan.</p>

        @forelse ($opnam->items as $item)
            @php
                $materialSekarang = $item->tariff?->materialSpec;
                $jenisSekarang = $materialSekarang?->workType;
                $kategoriSekarang = $jenisSekarang?->workCategory;
            @endphp
            <div class="card" style="background:#f8fafc; margin-bottom:14px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <strong>Item Pekerjaan {{ $loop->iteration }}</strong>
                    <span class="muted" style="font-size:12px;">Subtotal tersimpan: <strong>{{ $rupiah($item->subtotal) }}</strong></span>
                </div>

                <form method="POST" action="{{ $urls['item_update']($item) }}">
                    @csrf
                    @method('PUT')
                    <div class="grid">
                        <div>
                            <label>Kategori Pekerjaan *</label>
                            <select name="work_category_id" required>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string) old('work_category_id', $kategoriSekarang?->id) === (string) $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>Jenis Pekerjaan *</label>
                            <select name="work_type_id" required>
                                @foreach ($categories as $category)
                                    <optgroup label="{{ $category->name }}">
                                        @foreach ($workTypes->where('work_category_id', $category->id) as $workType)
                                            <option value="{{ $workType->id }}" @selected((string) old('work_type_id', $jenisSekarang?->id) === (string) $workType->id)>{{ $workType->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>Material *</label>
                            <select name="material_spec_id" required>
                                @foreach ($workTypes as $workType)
                                    <optgroup label="{{ $workType->name }}">
                                        @foreach ($materialSpecs->where('work_type_id', $workType->id) as $material)
                                            <option value="{{ $material->id }}" @selected((string) old('material_spec_id', $materialSekarang?->id) === (string) $material->id)>{{ $material->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>Satuan *</label>
                            <select name="unit_id" required>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}" @selected((string) old('unit_id', $item->tariff?->unit_id) === (string) $unit->id)>{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div><label>Kuantitas *</label><input type="number" name="volume" step="0.01" min="0" required value="{{ old('volume', $item->volume) }}"></div>
                        <div><label>Ketebalan Pelat (mm)</label><input type="number" name="ketebalan" min="0" value="{{ old('ketebalan', $item->ketebalan) }}"></div>
                        <div>
                            <label>Kompleksitas *</label>
                            <select name="kompleksitas" required>
                                @foreach (['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'] as $nilai => $label)
                                    <option value="{{ $nilai }}" @selected(old('kompleksitas', $item->kompleksitas ?? 'low') === $nilai)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div style="margin-top:12px;"><label>Deskripsi Pekerjaan *</label><textarea name="description" rows="2" required>{{ old('description', $item->description) }}</textarea></div>
                    <div style="margin-top:12px;"><label>Catatan Tambahan</label><textarea name="catatan_tambahan" rows="2">{{ old('catatan_tambahan', $item->catatan_tambahan) }}</textarea></div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:12px;">
                        <small class="muted">Harga satuan tersimpan: {{ $rupiah($item->unit_price) }} &nbsp;•&nbsp; Faktor: {{ $item->faktor }}x</small>
                        <button type="submit" class="btn btn-primary">Simpan Item</button>
                    </div>
                </form>

                <form method="POST" action="{{ $urls['item_destroy']($item) }}" onsubmit="return confirm('Hapus item ini dan lampirannya?');" style="margin-top:8px;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-light" style="padding:5px 10px; color:#991b1b;">Hapus Item</button>
                </form>
            </div>
        @empty
            <p class="muted">Belum ada item pekerjaan.</p>
        @endforelse
    </div>

    <div class="card">
        <h3 style="margin:0 0 4px;">Tambah Item Pekerjaan</h3>
        <p class="muted" style="margin:0 0 16px;">Tarif dicari otomatis dari material dan satuan yang berlaku pada tanggal opnam.</p>
        <form method="POST" action="{{ $urls['tambah_item'] }}">
            @csrf
            <div class="grid">
                <div>
                    <label>Kategori Pekerjaan *</label>
                    <select name="work_category_id" required>
                        <option value="">-- Pilih --</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('work_category_id') === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Jenis Pekerjaan *</label>
                    <select name="work_type_id" required>
                        <option value="">-- Pilih --</option>
                        @foreach ($workTypes as $workType)
                            <option value="{{ $workType->id }}" @selected(old('work_type_id') === (string) $workType->id)>{{ $workType->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Material *</label>
                    <select name="material_spec_id" required>
                        <option value="">-- Pilih --</option>
                        @foreach ($materialSpecs as $material)
                            <option value="{{ $material->id }}" @selected(old('material_spec_id') === (string) $material->id)>{{ $material->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Satuan *</label>
                    <select name="unit_id" required>
                        <option value="">-- Pilih --</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" @selected(old('unit_id') === (string) $unit->id)>{{ $unit->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label>Kuantitas *</label><input type="number" name="volume" step="0.01" min="0" required value="{{ old('volume') }}"></div>
                <div><label>Ketebalan Pelat (mm)</label><input type="number" name="ketebalan" min="0" value="{{ old('ketebalan') }}"></div>
                <div>
                    <label>Kompleksitas *</label>
                    <select name="kompleksitas" required>
                        @foreach (['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'] as $nilai => $label)
                            <option value="{{ $nilai }}" @selected(old('kompleksitas', 'low') === $nilai)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div style="margin-top:12px;"><label>Deskripsi Pekerjaan *</label><textarea name="description" rows="2" required>{{ old('description') }}</textarea></div>
            <div style="margin-top:12px;"><label>Catatan Tambahan</label><textarea name="catatan_tambahan" rows="2">{{ old('catatan_tambahan') }}</textarea></div>
            <div style="margin-top:12px;"><button type="submit" class="btn btn-primary">+ Tambah Item</button></div>
        </form>
    </div>
@endsection

@push('scripts')
    @php
        $unitJson = $units->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])->values();
        $tariffJson = $tariffs->map(fn ($t) => [
            'material' => $t->material_spec_id,
            'unit' => $t->unit_id,
            'effective_from' => (string) $t->effective_from,
        ])->values();
    @endphp
    <script>
        const units = @json($unitJson);
        const tariffs = @json($tariffJson);
        const tanggalOpnam = @json($tanggalOpnam ? $tanggalOpnam->toDateString() : now()->toDateString());
        const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

        const unitOptions = (material, selected) => {
            const daftar = material
                ? tariffs
                    .filter((t) => String(t.effective_from).slice(0, 10) <= tanggalOpnam
                        && String(t.material) === String(material))
                    .map((t) => ({ id: t.unit, name: units.find((u) => String(u.id) === String(t.unit))?.name || '' }))
                    .filter((u, i, arr) => arr.findIndex((x) => x.name === u.name) === i)
                : units;
            return '<option value="">-- Pilih --</option>' + daftar
                .map((o) => `<option value="${o.id}" ${String(o.id) === String(selected) ? 'selected' : ''}>${esc(o.name)}</option>`)
                .join('');
        };

        document.querySelectorAll('form').forEach((form) => {
            const material = form.querySelector('select[name="material_spec_id"]');
            const unit = form.querySelector('select[name="unit_id"]');
            if (!material || !unit) return;
            unit.innerHTML = unitOptions(material.value, unit.value);
            material.addEventListener('change', () => {
                unit.innerHTML = unitOptions(material.value, null);
            });
        });
    </script>
@endpush
