@extends('layouts.simship')

@section('title', 'Buat Opname')
@section('crumb', 'Buat Opname')

@section('content')
    @php
        $steps = [1 => 'Informasi Kapal', 2 => 'Detail Pekerjaan', 3 => 'Estimasi Biaya', 4 => 'Bukti Pekerjaan', 5 => 'Final Review'];
        $shipSatu = $ships->firstWhere('id', $draft['ship_id'] ?? null);
        $rupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
        $subtotalDraft = collect($draft['items'] ?? [])->sum('subtotal');
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
                        <div style="margin-top:12px;"><label>Catatan (opsional)</label><textarea name="catatan" rows="2" placeholder="Tambahkan catatan umum untuk opnam ini...">{{ old('catatan', $draft['catatan'] ?? '') }}</textarea></div>
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
                            <thead><tr><th>PEKERJAAN</th><th>QTY</th><th>HARGA SATUAN</th><th>FAKTOR KETEBALAN</th><th>FAKTOR KOMPLEKSITAS</th><th>SUBTOTAL</th></tr></thead>
                            <tbody>
                                @foreach ($draft['items'] ?? [] as $item)
                                    <tr>
                                        <td><strong>{{ $item['work_type_name'] }}</strong><br><small style="color:#94a3b8;">{{ $item['material_name'] }}</small></td>
                                        <td>{{ $item['volume'] }} {{ $item['unit_name'] }}</td>
                                        <td>{{ $rupiah($item['unit_price']) }}</td>
                                        <td>{{ $item['faktor_ketebalan'] ?? 1 }}x</td>
                                        <td>{{ $item['faktor_kompleksitas'] ?? 1 }}x</td>
                                        <td>{{ $rupiah($item['subtotal']) }}</td>
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
                        @forelse ($draft['items'] ?? [] as $item)
                            @php $uid = $item['uid'] ?? null; @endphp
                            @if ($uid)
                                <div class="card" style="background:#fff; margin-bottom:12px;">
                                    <strong>{{ $loop->iteration }}. {{ $item['work_type_name'] ?? 'Item Pekerjaan' }}</strong>
                                    <label class="dropzone" for="bukti-{{ $loop->iteration }}" style="margin-top:10px; padding:24px;">
                                        <div style="font-size:22px;">↑</div>
                                        <strong>Klik untuk mengunggah atau tarik file ke sini</strong><br>
                                        <small>JPG, PNG, atau PDF • Maksimal 10 MB per file</small>
                                        <input type="file" id="bukti-{{ $loop->iteration }}" name="bukti[{{ $uid }}][]" multiple accept=".jpg,.jpeg,.png,.pdf" style="display:none;">
                                    </label>
                                    <div class="selected-files" style="margin-top:8px; color:#475569; font-size:12px;"></div>
                                    @if (! empty($draft['uploads'][$uid]))
                                        <ul style="margin-top:10px; color:#475569;">
                                            @foreach ($draft['uploads'][$uid] as $upload)
                                                <li>{{ $upload['original_name'] }} ({{ number_format($upload['file_size'] / 1024, 0) }} KB)</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            @endif
                        @empty
                            <p style="color:#94a3b8;">Belum ada item pekerjaan.</p>
                        @endforelse
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
                                <div><small style="color:#64748b;">Catatan</small><br><strong>{{ $draft['catatan'] ?? '-' }}</strong></div>
                            </div>
                        </div>

                        <div class="card" style="background:#fff;">
                            <div style="display:flex; justify-content:space-between;"><strong>Detail Pekerjaan</strong><a href="{{ route('petugas.opnams.create', ['step' => 2]) }}">Ubah</a></div>
                            @foreach ($draft['items'] ?? [] as $i => $item)
                                <div style="display:flex; justify-content:space-between; margin-top:10px;">
                                    <div><strong>{{ $i + 1 }}. {{ $item['work_type_name'] }}</strong><br><small style="color:#94a3b8;">{{ $item['volume'] }} {{ $item['unit_name'] }} • {{ $item['material_name'] }} • Faktor ketebalan {{ $item['faktor_ketebalan'] ?? 1 }}x • Faktor kompleksitas {{ $item['faktor_kompleksitas'] ?? 1 }}x</small></div>
                                    <strong>{{ $rupiah($item['subtotal']) }}</strong>
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
        $tariffJson = $tariffs->map(fn ($t) => ['material' => $t->material_spec_id, 'unit' => $t->unit_id, 'price' => (float) $t->price, 'effective_from' => (string) $t->effective_from])->values();
        $faktorKetebalanJson = config('opnam.faktor_ketebalan');
        $faktorKompleksitasJson = config('opnam.faktor_kompleksitas');
    @endphp
    <script>
        const ships = @json($shipJson);
        const categories = @json($categoryJson);
        const types = @json($typeJson);
        const materials = @json($materialJson);
        const units = @json($unitJson);
        const tariffs = @json($tariffJson);
        const faktorKetebalan = @json($faktorKetebalanJson);
        const faktorKompleksitas = @json($faktorKompleksitasJson);
        const draftItems = @json($draft['items'] ?? []);
        // Tanggal opnam diambil dari session draft supaya tetap tersedia di
        // langkah 2-5, tempat input tanggal tidak dirender.
        const tanggalOpnamDariDraft = @json($tanggalOpnam ?? null);
        const tanggalOpnamInput = document.querySelector('input[name="tanggal_opnam"]');
        const tanggalEfektif = () => tanggalOpnamInput?.value
            || tanggalOpnamDariDraft
            || new Date().toISOString().slice(0, 10);
        const tarifBerlaku = () => {
            const tanggal = tanggalEfektif();
            return tariffs.filter((t) => String(t.effective_from).slice(0, 10) <= tanggal);
        };

        const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
        const rupiah = (n) => 'Rp ' + Number(n || 0).toLocaleString('id-ID');
        const formatFaktor = (n) => Number(n || 1).toFixed(2).replace(/\.00$/, '').replace(/0$/, '') + 'x';
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
            function updateItemTariffRow(row) {
                const material = row.querySelector('.material').value;
                const unit = row.querySelector('.unit').value;
                const volume = Number(row.querySelector('.volume').value || 0);
                const kompleksitas = row.querySelector('.kompleksitas:checked')?.value || 'low';
                const ketebalan = row.querySelector('.ketebalan').value;
                const faktorTebal = faktorKetebalan[ketebalan] ?? 1;
                const faktorKompleks = faktorKompleksitas[kompleksitas] ?? 1;
                const tariff = tarifBerlaku().find((t) => String(t.material) === material && String(t.unit) === unit);
                const unitPrice = tariff ? tariff.price : 0;
                const subtotal = unitPrice * volume * faktorTebal * faktorKompleks;
                const target = row.querySelector('.tarif-dasar-row');
                if (!target) return;
                target.querySelector('.tarif-harga').textContent = rupiah(unitPrice);
                target.querySelector('.tarif-satuan').textContent = units.find((u) => String(u.id) === unit)?.name || '';
                target.querySelector('.faktor-badge').textContent = `Faktor tebal ${faktorTebal}x / kompleksitas ${faktorKompleks}x (${kompleksitas})`;
                target.querySelector('.item-amount').textContent = rupiah(subtotal);
            }

            function updateSummary() {
                let total = 0;
                let faktorUtama = 1;
                let barisPertama = true;
                container.querySelectorAll('.item-row').forEach((row) => {
                    updateItemTariffRow(row);
                    const material = row.querySelector('.material').value;
                    const unit = row.querySelector('.unit').value;
                    const volume = Number(row.querySelector('.volume').value || 0);
                    const kompleksitas = row.querySelector('.kompleksitas:checked')?.value || 'low';
                    const ketebalan = row.querySelector('.ketebalan').value;
                    const faktorTebal = faktorKetebalan[ketebalan] ?? 1;
                    const faktorKompleks = faktorKompleksitas[kompleksitas] ?? 1;
                    if (barisPertama) {
                        faktorUtama = faktorTebal * faktorKompleks;
                        barisPertama = false;
                    }
                    const tariff = tarifBerlaku().find((t) => String(t.material) === material && String(t.unit) === unit);
                    total += (tariff ? tariff.price * volume : 0) * faktorTebal * faktorKompleks;
                });
                document.getElementById('item-count').textContent = container.querySelectorAll('.item-row').length + ' item';
                document.getElementById('subtotal').textContent = rupiah(total);
                document.getElementById('faktor-tambahan').textContent = formatFaktor(faktorUtama);
                document.getElementById('total').textContent = rupiah(total);
            }

            function updateKompleksitasStyle(row) {
                row.querySelectorAll('.kompleksitas-label').forEach((label) => {
                    const aktif = label.querySelector('input').checked;
                    label.style.background = aktif ? '#dbeafe' : '#fff';
                    label.style.color = aktif ? '#1d4ed8' : '#475569';
                });
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
                const uid = data?.uid || 'item-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 7);
                const row = document.createElement('div');
                row.className = 'card item-row';
                row.dataset.index = i;
                row.style.background = '#f8fafc';
                row.innerHTML = `
                    <input type="hidden" name="items[${i}][uid]" value="${esc(uid)}">
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
                        <div><label>Ketebalan Pelat (mm)</label>
                            <input type="number" name="items[${i}][ketebalan]" class="ketebalan" min="0" value="${esc(data?.ketebalan ?? '')}">
                        </div>
                        <div><label>Kompleksitas Pekerjaan *</label>
                            <div style="display:flex; border:1px solid #cbd5e1; border-radius:6px; overflow:hidden;">
                                ${['low', 'medium', 'high'].map((k) => {
                                    const aktif = (data?.kompleksitas ?? 'low') === k;
                                    return `<label class="kompleksitas-label" style="flex:1; text-align:center; padding:8px 0; cursor:pointer; font-size:12px; font-weight:600; background:${aktif ? '#dbeafe' : '#fff'}; color:${aktif ? '#1d4ed8' : '#475569'};">
                                        <input type="radio" name="items[${i}][kompleksitas]" value="${k}" class="kompleksitas" ${aktif ? 'checked' : ''} style="display:none;">
                                        ${k.charAt(0).toUpperCase() + k.slice(1)}
                                    </label>`;
                                }).join('')}
                            </div>
                        </div>
                    </div>
                    <div style="margin-top:12px;"><label>Deskripsi Pekerjaan *</label>
                        <textarea name="items[${i}][description]" rows="2" required placeholder="Jelaskan lokasi dan lingkup pekerjaan...">${esc(data?.description ?? '')}</textarea>
                    </div>
                    <div style="margin-top:12px;"><label>Catatan Tambahan</label>
                        <textarea name="items[${i}][catatan_tambahan]" rows="2" placeholder="Tambahkan catatan bila diperlukan...">${esc(data?.catatan_tambahan ?? '')}</textarea>
                    </div>
                    <div class="tarif-dasar-row" style="display:flex; justify-content:space-between; align-items:center; margin-top:10px; padding:8px; background:#f8fafc; border-radius:6px; font-size:12px;">
                        <span>Tarif dasar 🔒 <span class="tarif-harga">${rupiah(data?.unit_price ?? 0)}</span> / <span class="tarif-satuan">${esc(data?.unit_name || '')}</span>
                            <span class="faktor-badge" style="background:#fef3c7; color:#92400e; padding:2px 6px; border-radius:999px; margin-left:6px;">Faktor ${esc(data?.kompleksitas || 'low')}: ${data?.faktor ?? 1}x</span>
                        </span>
                        <strong class="item-amount">${rupiah((data?.subtotal || 0) + (data?.adjustment || 0))}</strong>
                    </div>`;
                container.appendChild(row);
                updateKompleksitasStyle(row);
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
                if (e.target.classList.contains('kompleksitas')) {
                    updateKompleksitasStyle(row);
                }
                updateSummary();
            });

            container.addEventListener('input', updateSummary);
            if (tanggalOpnamInput) {
                tanggalOpnamInput.addEventListener('change', updateSummary);
            }

            if (draftItems.length) {
                draftItems.forEach((item) => addItem(item));
            } else {
                addItem(null);
            }
        }

        document.querySelectorAll('.dropzone').forEach((zone) => {
            const input = zone.querySelector('input[type=file]');
            if (!input) return;
            const list = zone.parentElement.querySelector('.selected-files');
            const renderNames = () => {
                if (list) {
                    list.textContent = input.files.length
                        ? [...input.files].map((file) => file.name).join(', ')
                        : '';
                }
            };

            zone.addEventListener('dragenter', (e) => {
                e.preventDefault();
                zone.classList.add('dragover');
            });
            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                zone.classList.add('dragover');
            });
            zone.addEventListener('dragleave', (e) => {
                e.preventDefault();
                zone.classList.remove('dragover');
            });
            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                e.stopPropagation();
                zone.classList.remove('dragover');
                const transfer = new DataTransfer();
                [...(e.dataTransfer?.files || [])].forEach((file) => transfer.items.add(file));
                input.files = transfer.files;
                renderNames();
            });
            input.addEventListener('change', renderNames);
        });
    </script>
@endpush
