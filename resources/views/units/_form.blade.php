<div style="margin-bottom:16px;">
    <label for="code">Kode satuan <span aria-hidden="true">*</span></label>
    <input id="code" type="text" name="code" value="{{ old('code', $unit->code ?? '') }}" required maxlength="255" autofocus>
    @error('code')<div class="error">{{ $message }}</div>@enderror
</div>

<div style="margin-bottom:16px;">
    <label for="name">Nama satuan <span aria-hidden="true">*</span></label>
    <input id="name" type="text" name="name" value="{{ old('name', $unit->name ?? '') }}" required maxlength="255">
    @error('name')<div class="error">{{ $message }}</div>@enderror
</div>

<div style="display:flex; gap:8px;">
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    <a href="{{ route('admin.units.index') }}" class="btn btn-light">Batal</a>
</div>
