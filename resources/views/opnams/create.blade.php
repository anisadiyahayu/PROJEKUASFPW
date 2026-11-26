@extends('layouts.simship')

@section('title', 'Buat Opname')
@section('crumb', 'Buat Opname')

@section('content')
    @php
        $steps = [1 => 'Informasi Kapal', 2 => 'Detail Pekerjaan', 3 => 'Estimasi Biaya', 4 => 'Bukti Pekerjaan', 5 => 'Final Review'];
        $shipSatu = $ships->firstWhere('id', $draft['ship_id'] ?? null);
        $rupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
        $baseDraft = collect($draft['items'] ?? [])->sum('subtotal');
        $adjustmentDraft = collect($draft['items'] ?? [])->sum('adjustment');
        $subtotalDraft = $baseDraft + $adjustmentDraft;
    @endphp

    <h1>Buat Opname</h1>
    <p class="subtitle">Catat detail inspeksi dan estimasi pekerjaan perbaikan kapal.</p>

    <div class="steps">
        @foreach ($steps as $n => $label)
            @php $class = 'step'.($n === $step ? ' active' : ($n < $step ? ' done' : '')); @endphp
            @if ($n <= $step)
                <a href="{{ route('petugas.opnams.create', ['step' => $n]) }}" class="{{ $class }}">
                    <span class="num">{{ $n < $step ? '✓' : $n }}</span>{{ $label }}
                </a>
            @else
                <span class="{{ $class }}"><span class="num">{{ $n }}</span>{{ $label }}</span>
            @endif
        @endforeach
    </div>

    @if ($errors->any())
        <div class="card" style="border-color:#fecaca; background:#fef2f2; color:#991b1b;">
            @foreach ($errors->all() as $error) <div>{{ $error }}</div> @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('petugas.opnams.store-step') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="step" value="{{ $step }}">

        <div class="two-col">
            <div>
                @if ($step === 1)
                    <div class="card">
                        <h3 style="margin:0 0 4px;">01 &nbsp; Informasi Kapal</h3>
                        <p style="color:#64748b; font-size:12px; margin:0 0 16px;">Pilih kapal terdaftar untuk mengisi data secara otomatis.</p>
                        <div style="margin-bottom:14px;">
                            <label>Pilih Kapal *</label>
                            <select id="pilih-kapal" name="ship_id" required>
                                <option value="">-- Pilih Kapal --</option>
                                @foreach ($ships as $ship)
                                    <option value="{{ $ship->id }}" @selected((string) ($draft['ship_id'] ?? '') === (string) $ship->id)>{{ $ship->name }} — {{ $ship->hull_number }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid">
                            <div><label>Nama Kapal</label><input type="text" id="ship-name" readonly value="{{ $shipSatu?->name ?? '' }}"></div>
                            <div><label>Nomor Lambung</label><input type="text" id="ship-hull" readonly value="{{ $shipSatu?->hull_number ?? '' }}"></div>
                            <div><label>Pemilik Kapal</label><input type="text" id="ship-owner" readonly value="{{ $shipSatu?->owner?->name ?? '' }}"></div>
                            <div><label>Jenis Kapal</label><input type="text" id="ship-type" readonly value="{{ $shipSatu?->jenis_kapal ?? '' }}"></div>
                            <div><label>Lokasi Pekerjaan *</label>
                                <select name="lokasi" required>
                                    <option value="Dock 01 — Graving Dock" @selected(old('lokasi', $draft['lokasi'] ?? '') === 'Dock 01 — Graving Dock')>Dock 01 — Graving Dock</option>
                                    <option value="Dock 02 — Floating Dock" @selected(old('lokasi', $draft['lokasi'] ?? '') === 'Dock 02 — Floating Dock')>Dock 02 — Floating Dock</option>
                                </select>
                            </div>
                            <div><label>Tanggal Opname *</label><input type="date" name="tanggal_opnam" required value="{{ old('tanggal_opnam', $draft['tanggal_opnam'] ?? '') }}"></div>
                        </div>
                        <div class="info">🔒 Informasi kapal diisi otomatis dari data master dan tidak dapat diubah.</div>
                    </div>
                @elseif ($step === 2)
                    <div class="card">
                        <h3 style="margin:0 0 4px;">02 &nbsp; Detail Pekerjaan</h3>
                        <p style="color:#64748b; font-size:12px; margin:0 0 16px;">Tambahkan seluruh item pekerjaan berdasarkan hasil inspeksi.</p>
                        <div id="items"></div>
                        <button type="button" id="add-item" class="btn btn-light" style="width:100%; border-style:dashed;">+ &nbsp; Tambah Item Pekerjaan</button>
                    </div>
                @elseif ($step === 3)
                    <div class="card">
                        <h3 style="margin:0 0 4px;">03 &nbsp; Estimasi Biaya</h3>
                        <p style="color:#64748b; font-size:12px; margin:0 0 16px;">Periksa hasil perhitungan berdasarkan tarif yang berlaku.</p>
                        <table>
                            <thead><tr><th>PEKERJAAN</th><th>QTY</th><th>HARGA SATUAN</th><th>FAKTOR</th><th>SUBTOTAL</th></tr></thead>
                            <tbody>
                                @foreach ($draft['items'] ?? [] as $item)
                                    <tr>
                                        <td><strong>{{ $item['work_type_name'] }}</strong><br><small style="color:#94a3b8;">{{ $item['material_name'] }}</small></td>
                                        <td>{{ $item['volume'] }} {{ $item['unit_name'] }}</td>
                                        <td>{{ $rupiah($item['unit_price']) }}</td>
                                        <td>{{ $item['faktor'] }}x</td>
                                        <td>{{ $rupiah($item['subtotal'] + $item['adjustment']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div style="text-align:right; padding:12px 8px; background:#f8fafc;">Total Estimasi Biaya &nbsp; <strong style="color:#1e3a8a; font-size:18px;">{{ $rupiah($subtotalDraft) }}</strong></div>
                        <div class="info">🔒 Tarif bersumber dari data master dan tidak dapat diubah Operator.</div>
                    </div>
                @elseif ($step === 4)
                    <div class="card">
                        <h3 style="margin:0 0 4px;">04 &nbsp; Upload Bukti Pekerjaan</h3>
                        <p style="color:#64748b; font-size:12px; margin:0 0 16px;">Unggah foto kondisi kapal untuk mendukung hasil inspeksi.</p>
                        <label class="dropzone" for="bukti">
                            <div style="font-size:26px;">↑</div>
                            <strong>Klik untuk mengunggah atau tarik file ke sini</strong><br>
                            <small>JPG, PNG, atau PDF • Maksimal 10 MB per file</small>
                            <input type="file" id="bukti" name="bukti[]" multiple accept=".jpg,.jpeg,.png,.pdf" style="display:none;">
                        </label>
                        @if (! empty($draft['uploads']))
                            <p style="margin-top:12px; color:#475569;">{{ count($draft['uploads']) }} file sudah diunggah:</p>
                            <ul>
                                @foreach ($draft['uploads'] as $upload)
                                    <li>{{ $upload['original_name'] }} ({{ number_format($upload['file_size'] / 1024, 0) }} KB)</li>
                                @endforeach
                            </ul>
                        @endif
                        <div class="info">Unggah bersifat opsional.</div>
                    </div>
                @else
                    <div class="card">
                        <h3 style="margin:0 0 4px;">05 &nbsp; Final Review</h3>
                        <p style="color:#64748b; font-size:12px; margin:0 0 16px;">Pastikan seluruh informasi sudah benar sebelum disimpan.</p>

                        <div class="card" style="background:#fff;">
                            <div style="display:flex; justify-content:space-between;"><strong>Informasi Kapal</strong><a href="{{ route('petugas.opnams.create', ['step' => 1]) }}">Ubah</a></div>
                            <div class="grid" style="margin-top:10px;">
                                <div><small style="color:#64748b;">Nama Kapal</small><br><strong>{{ $shipSatu?->name ?? '-' }}</strong></div>
                                <div><small style="color:#64748b;">Nomor Lambung</small><br><strong>{{ $shipSatu?->hull_number ?? '-' }}</strong></div>
                                <div><small style="color:#64748b;">Pemilik</small><br><strong>{{ $shipSatu?->owner?->name ?? '-' }}</strong></div>
                                <div><small style="color:#64748b;">Jenis Kapal</small><br><strong>{{ $shipSatu?->jenis_kapal ?? '-' }}</strong></div>
                                <div><small style="color:#64748b;">Lokasi Pekerjaan</small><br><strong>{{ $draft['lokasi'] ?? '-' }}</strong></div>
                                <div><small style="color:#64748b;">Tanggal Opname</small><br><strong>{{ $draft['tanggal_opnam'] ?? '-' }}</strong></div>
                            </div>
                        </div>

                        <div class="card" style="background:#fff;">
                            <div style="display:flex; justify-content:space-between;"><strong>Detail Pekerjaan</strong><a href="{{ route('petugas.opnams.create', ['step' => 2]) }}">Ubah</a></div>
                            @foreach ($draft['items'] ?? [] as $i => $item)
                                <div style="display:flex; justify-content:space-between; margin-top:10px;">
                                    <div><strong>{{ $i + 1 }}. {{ $item['work_type_name'] }}</strong><br><small style="color:#94a3b8;">{{ $item['volume'] }} {{ $item['unit_name'] }} • {{ $item['material_name'] }} • Faktor {{ $item['faktor'] }}x</small></div>
                                    <strong>{{ $rupiah($item['subtotal'] + $item['adjustment']) }}</strong>
                                </div>
                            @endforeach
                        </div>

                        <div class="card" style="background:#fff;">
                            <div style="display:flex; justify-content:space-between;"><strong>Bukti Pekerjaan</strong><a href="{{ route('petugas.opnams.create', ['step' => 4]) }}">Ubah</a></div>
                            <p style="color:#64748b;">{{ count($draft['uploads'] ?? []) }} file bukti akan dikirim bersama pengajuan.</p>
                        </div>
                    </div>
                @endif
            </div>

            @include('opnams._summary')
        </div>

        <div class="card" style="display:flex; justify-content:space-between; align-items:center;">
            @if ($step > 1)
                <a class="btn btn-light" href="{{ route('petugas.opnams.create', ['step' => $step - 1]) }}">Kembali</a>
            @else
                <span></span>
            @endif

            @if ($step === 5)
                <div style="display:flex; gap:8px;">
                    <button type="submit" class="btn btn-light">Simpan Draft</button>
                    <button type="button" class="btn btn-primary" disabled title="Menunggu penetapan pemilik PT-07">Kirim untuk Review</button>
                </div>
            @else
                <button type="submit" class="btn btn-primary">Lanjutkan →</button>
            @endif
        </div>
    </form>
@endsection

@push('scripts')
    @php
        $shipJson = $ships->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'hull' => $s->hull_number, 'owner' => $s->owner?->name ?? '', 'type' => $s->jenis_kapal ?? ''])->values();
        $categoryJson = $categories->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])->values();
        $typeJson = $workTypes->map(fn ($t) => ['id' => $t->id, 'category' => $t->work_category_id, 'name' => $t->name])->values();
        $materialJson = $materialSpecs->map(fn ($m) => ['id' => $m->id, 'type' => $m->work_type_id, 'name' => $m->name])->values();
        $unitJson = $units->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])->values();
        $tariffJson = $tariffs->map(fn ($t) => ['material' => $t->material_spec_id, 'unit' => $t->unit_id, 'price' => (float) $t->price])->values();
    @endphp
    <script>
        const ships = @json($shipJson);
        const categories = @json($categoryJson);
        const types = @json($typeJson);
        const materials = @json($materialJson);
        const units = @json($unitJson);
        const tariffs = @json($tariffJson);
        const draftItems = @json($draft['items'] ?? []);
        const defaultLokasi = @json($draft['lokasi'] ?? '');

        const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
        const rupiah = (n) => 'Rp ' + Number(n || 0).toLocaleString('id-ID');
        const options = (list, selected, valueKey, labelKey, filter) => list
            .filter(filter || (() => true))
            .map((o) => `<option value="${o[valueKey]}" ${String(o[valueKey]) === String(selected) ? 'selected' : ''}>${esc(o[labelKey])}</option>`)
            .join('');

        const shipSelect = document.getElementById('pilih-kapal');
        if (shipSelect) {
            shipSelect.addEventListener('change', () => {
                const ship = ships.find((s) => String(s.id) === shipSelect.value);
                document.getElementById('ship-name').value = ship ? ship.name : '';
                document.getElementById('ship-hull').value = ship ? ship.hull : '';
                document.getElementById('ship-owner').value = ship ? ship.owner : '';
                document.getElementById('ship-type').value = ship ? ship.type : '';
            });
        }

        const container = document.getElementById('items');
        if (container) {
            function updateSummary() {
                let base = 0;
                let adjustment = 0;
                container.querySelectorAll('.item-row').forEach((row) => {
                    const material = row.querySelector('.material').value;
                    const unit = row.querySelector('.unit').value;
                    const volume = Number(row.querySelector('.volume').value || 0);
                    const faktor = Number(row.querySelector('.faktor').value || 1);
                    const tariff = tariffs.find((t) => String(t.material) === material && String(t.unit) === unit);
                    const amount = tariff ? tariff.price * volume : 0;
                    base += amount;
                    adjustment += amount * (faktor - 1);
                });
                document.getElementById('subtotal').textContent = rupiah(base);
                document.getElementById('faktor-tambahan').textContent = rupiah(adjustment);
                document.getElementById('total').textContent = rupiah(base + adjustment);
            }

            function renumber() {
                container.querySelectorAll('.item-row').forEach((row, i) => {
                    row.dataset.index = i;
                    row.querySelectorAll('[name]').forEach((el) => {
                        el.name = el.name.replace(/items\[\d+\]/, `items[${i}]`);
                    });
                    row.querySelector('.item-title').textContent = `Item Pekerjaan ${i + 1}`;
                });
                updateSummary();
            }

            function addItem(data) {
                const i = container.querySelectorAll('.item-row').length;
                const row = document.createElement('div');
                row.className = 'card item-row';
                row.dataset.index = i;
                row.style.background = '#f8fafc';
                row.innerHTML = `
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                        <strong class="item-title">Item Pekerjaan ${i + 1}</strong>
                        <button type="button" class="btn btn-light remove-item" style="padding:5px 10px;">Hapus</button>
                    </div>
                    <div class="grid">
                        <div><label>Kategori Pekerjaan *</label>
                            <select name="items[${i}][work_category_id]" class="cat" required>
                                <option value="">-- Pilih --</option>${options(categories, data?.work_category_id, 'id', 'name')}
                            </select></div>
                        <div><label>Jenis Pekerjaan *</label>
                            <select name="items[${i}][work_type_id]" class="type" required>
                                <option value="">-- Pilih --</option>${options(types, data?.work_type_id, 'id', 'name', (t) => String(t.category) === String(data?.work_category_id))}
                            </select></div>
                        <div><label>Material *</label>
                            <select name="items[${i}][material_spec_id]" class="material" required>
                                <option value="">-- Pilih --</option>${options(materials, data?.material_spec_id, 'id', 'name', (m) => String(m.type) === String(data?.work_type_id))}
                            </select></div>
                        <div><label>Spesifikasi Material</label>
                            <input type="text" class="spec" readonly value="${esc(materials.find((m) => String(m.id) === String(data?.material_spec_id))?.name || '')}">
                        </div>
                        <div><label>Kuantitas *</label>
                            <input type="number" name="items[${i}][volume]" class="volume" step="0.01" min="0" required value="${esc(data?.volume ?? '')}">
                        </div>
                        <div><label>Satuan *</label>
                            <select name="items[${i}][unit_id]" class="unit" required>
                                <option value="">-- Pilih --</option>${options(units, data?.unit_id, 'id', 'name')}
                            </select></div>
                        <div><label>Faktor Tambahan *</label>
                            <select name="items[${i}][faktor]" class="faktor" required>
                                <option value="1" ${String(data?.faktor ?? 1) === '1' ? 'selected' : ''}>1x</option>
                                <option value="1.25" ${String(data?.faktor ?? 1) === '1.25' ? 'selected' : ''}>1,25x</option>
                            </select></div>
                        <div><label>Lokasi Pekerjaan</label>
                            <input type="text" name="items[${i}][lokasi]" value="${esc(data?.lokasi ?? defaultLokasi)}">
                        </div>
                    </div>
                    <div style="margin-top:12px;"><label>Deskripsi Pekerjaan *</label>
                        <textarea name="items[${i}][description]" rows="2" required placeholder="Jelaskan lokasi dan lingkup pekerjaan...">${esc(data?.description ?? '')}</textarea>
                    </div>`;
                container.appendChild(row);
                updateSummary();
            }

            document.getElementById('add-item').addEventListener('click', () => addItem(null));

            container.addEventListener('click', (e) => {
                if (e.target.classList.contains('remove-item')) {
                    e.target.closest('.item-row').remove();
                    renumber();
                }
            });

            container.addEventListener('change', (e) => {
                const row = e.target.closest('.item-row');
                if (!row) return;
                if (e.target.classList.contains('cat')) {
                    row.querySelector('.type').innerHTML = '<option value="">-- Pilih --</option>' + options(types, null, 'id', 'name', (t) => String(t.category) === e.target.value);
                    row.querySelector('.material').innerHTML = '<option value="">-- Pilih --</option>';
                    row.querySelector('.spec').value = '';
                }
                if (e.target.classList.contains('type')) {
                    row.querySelector('.material').innerHTML = '<option value="">-- Pilih --</option>' + options(materials, null, 'id', 'name', (m) => String(m.type) === e.target.value);
                    row.querySelector('.spec').value = '';
                }
                if (e.target.classList.contains('material')) {
                    row.querySelector('.spec').value = materials.find((m) => String(m.id) === e.target.value)?.name || '';
                }
                updateSummary();
            });

            container.addEventListener('input', updateSummary);

            if (draftItems.length) {
                draftItems.forEach((item) => addItem(item));
            } else {
                addItem(null);
            }
        }
    </script>
@endpush
