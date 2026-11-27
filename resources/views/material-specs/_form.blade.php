<div style="margin-bottom:16px;">
    <label for="work_type_id">Jenis pekerjaan <span aria-hidden="true">*</span></label>
    <select id="work_type_id" name="work_type_id" required>
        <option value="">Pilih jenis pekerjaan</option>
        @foreach ($workTypes as $workType)
            <option value="{{ $workType->id }}" @selected((string) old('work_type_id', $materialSpec->work_type_id ?? '') === (string) $workType->id)>
                {{ $workType->name }}{{ $workType->trashed() ? ' (dihapus)' : '' }}
            </option>
        @endforeach
    </select>
    @error('work_type_id')<div class="error">{{ $message }}</div>@enderror
</div>

<div style="margin-bottom:16px;">
    <label for="name">Nama material/spesifikasi <span aria-hidden="true">*</span></label>
    <input id="name" type="text" name="name" value="{{ old('name', $materialSpec->name ?? '') }}" required maxlength="255" autofocus>
    @error('name')<div class="error">{{ $message }}</div>@enderror
</div>

<div style="display:flex; gap:8px;">
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    <a href="{{ route('admin.material-specs.index') }}" class="btn btn-light">Batal</a>
</div>
