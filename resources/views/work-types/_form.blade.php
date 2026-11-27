<div style="margin-bottom:16px;">
    <label for="work_category_id">Kategori pekerjaan <span aria-hidden="true">*</span></label>
    @if (!empty($inactiveCategory))
        <p role="status">Kategori saat ini “{{ $inactiveCategory->name }}” nonaktif. Pilih kategori aktif untuk menyimpan perubahan.</p>
    @endif
    <select id="work_category_id" name="work_category_id" required>
        <option value="">Pilih kategori</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected((string) old('work_category_id', $workType->work_category_id ?? '') === (string) $category->id)>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
    @error('work_category_id')<div class="error">{{ $message }}</div>@enderror
</div>

<div style="margin-bottom:16px;">
    <label for="name">Nama jenis pekerjaan <span aria-hidden="true">*</span></label>
    <input id="name" type="text" name="name" value="{{ old('name', $workType->name ?? '') }}" required maxlength="255" autofocus>
    @error('name')<div class="error">{{ $message }}</div>@enderror
</div>

<div style="display:flex; gap:8px;">
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    <a href="{{ route('admin.work-types.index') }}" class="btn btn-light">Batal</a>
</div>
