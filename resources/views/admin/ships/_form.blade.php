@php
    $isEdit = $ship->exists;
    $action = $isEdit ? route('admin.ships.update', $ship) : route('admin.ships.store');
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="grid">
        <div>
            <label for="owner_id">Pemilik Kapal</label>
            <select id="owner_id" name="owner_id" required>
                <option value="">— Pilih pemilik —</option>
                @foreach ($owners as $owner)
                    <option value="{{ $owner->id }}" @selected((string) old('owner_id', $ship->owner_id) === (string) $owner->id)>
                        {{ $owner->name }}
                    </option>
                @endforeach
            </select>
            @if ($owners->isEmpty())
                <p class="field-error">Belum ada pemilik. <a href="{{ route('admin.owners.create') }}">Tambah pemilik terlebih dahulu</a>.</p>
            @endif
        </div>
        <div>
            <label for="name">Nama Kapal</label>
            <input id="name" type="text" name="name" value="{{ old('name', $ship->name) }}" maxlength="255" required>
        </div>
        <div>
            <label for="hull_number">Nomor Lambung</label>
            <input id="hull_number" type="text" name="hull_number" value="{{ old('hull_number', $ship->hull_number) }}" maxlength="255" required>
        </div>
        <div>
            <label for="jenis_kapal">Jenis Kapal</label>
            @php
                $jenisKapal = config('opnam.jenis_kapal', []);
                $jenisKapalSekarang = old('jenis_kapal', $ship->jenis_kapal);
                if ($jenisKapalSekarang && ! in_array($jenisKapalSekarang, $jenisKapal, true)) {
                    $jenisKapal[] = $jenisKapalSekarang;
                }
            @endphp
            <select id="jenis_kapal" name="jenis_kapal">
                <option value="">— Pilih jenis kapal —</option>
                @foreach ($jenisKapal as $opsi)
                    <option value="{{ $opsi }}" @selected((string) $jenisKapalSekarang === (string) $opsi)>{{ $opsi }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-actions" style="margin-top: 16px;">
        <a href="{{ route('admin.ships.index') }}" class="btn btn-light">Batal</a>
        <button type="submit" class="btn btn-primary" @disabled($owners->isEmpty())>
            {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Kapal' }}
        </button>
    </div>
</form>
