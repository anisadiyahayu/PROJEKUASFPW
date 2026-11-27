<div style="margin-bottom:16px;">
    <label for="name">Nama kategori <span aria-hidden="true">*</span></label>
    <input id="name" type="text" name="name" value="{{ old('name', $workCategory->name ?? '') }}" required maxlength="255" autofocus>
    @error('name')<div class="error">{{ $message }}</div>@enderror
</div>

<div style="margin-bottom:16px;">
    <label for="description">Deskripsi</label>
    <textarea id="description" name="description" rows="4">{{ old('description', $workCategory->description ?? '') }}</textarea>
    @error('description')<div class="error">{{ $message }}</div>@enderror
</div>

<div style="display:flex; gap:8px;">
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    <a href="{{ route('admin.work-categories.index') }}" class="btn btn-light">Batal</a>
</div>
