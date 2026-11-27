@extends('layouts.simship')

@section('title', 'Tambah Tarif Baru')
@section('crumb', 'Master Tarif / Tambah')

@section('content')
    <h1>Tambah Tarif Baru</h1>
    <p class="subtitle">Tarif baru menambah riwayat dan tidak mengubah tarif maupun transaksi yang sudah tersimpan.</p>

    <div class="card" style="max-width:760px;">
        <form method="POST" action="{{ route('admin.tariffs.store') }}">
            @csrf

            <div style="margin-bottom:16px;">
                <label for="material_spec_id">Material/spesifikasi <span aria-hidden="true">*</span></label>
                <select id="material_spec_id" name="material_spec_id" required autofocus>
                    <option value="">Pilih material/spesifikasi</option>
                    @foreach ($materialSpecs as $materialSpec)
                        <option value="{{ $materialSpec->id }}" @selected((string) old('material_spec_id') === (string) $materialSpec->id)>
                            {{ $materialSpec->name }} — {{ $materialSpec->workType?->name ?? 'Jenis pekerjaan tidak tersedia' }}{{ $materialSpec->workType?->trashed() ? ' (jenis pekerjaan dihapus)' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('material_spec_id')<div class="error">{{ $message }}</div>@enderror
            </div>

            <div style="margin-bottom:16px;">
                <label for="unit_id">Satuan <span aria-hidden="true">*</span></label>
                <select id="unit_id" name="unit_id" required>
                    <option value="">Pilih satuan</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" @selected((string) old('unit_id') === (string) $unit->id)>{{ $unit->name }} ({{ $unit->code }})</option>
                    @endforeach
                </select>
                @error('unit_id')<div class="error">{{ $message }}</div>@enderror
            </div>

            <div style="margin-bottom:16px;">
                <label for="price">Harga <span aria-hidden="true">*</span></label>
                <input id="price" type="number" name="price" value="{{ old('price') }}" required min="0" max="9999999999999.99" step="0.01">
                @error('price')<div class="error">{{ $message }}</div>@enderror
            </div>

            <div style="margin-bottom:16px;">
                <label for="effective_from">Tanggal berlaku <span aria-hidden="true">*</span></label>
                <input id="effective_from" type="date" name="effective_from" value="{{ old('effective_from') }}" required>
                @error('effective_from')<div class="error">{{ $message }}</div>@enderror
            </div>

            <div style="display:flex; gap:8px;">
                <button type="submit" class="btn btn-primary">Simpan Tarif Baru</button>
                <a href="{{ route('admin.tariffs.index') }}" class="btn btn-light">Batal</a>
            </div>
        </form>
    </div>
@endsection
