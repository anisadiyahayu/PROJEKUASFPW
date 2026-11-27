@php
    $isEdit = $owner->exists;
    $action = $isEdit ? route('admin.owners.update', $owner) : route('admin.owners.store');
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="grid">
        <div>
            <label for="name">Nama Pemilik</label>
            <input id="name" type="text" name="name" value="{{ old('name', $owner->name) }}" maxlength="255" required>
        </div>
        <div>
            <label for="address">Alamat</label>
            <textarea id="address" name="address" rows="3">{{ old('address', $owner->address) }}</textarea>
        </div>
    </div>

    <div class="form-actions" style="margin-top: 16px;">
        <a href="{{ route('admin.owners.index') }}" class="btn btn-light">Batal</a>
        <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Simpan Perubahan' : 'Tambah Pemilik' }}</button>
    </div>
</form>
