<div style="margin-bottom:16px;">
    <label for="name">Nama <span aria-hidden="true">*</span></label>
    <input id="name" type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required maxlength="255" autofocus>
    @error('name')<div class="error">{{ $message }}</div>@enderror
</div>

<div style="margin-bottom:16px;">
    <label for="username">Username <span aria-hidden="true">*</span></label>
    <input id="username" type="text" name="username" value="{{ old('username', $user->username ?? '') }}" required maxlength="255">
    @error('username')<div class="error">{{ $message }}</div>@enderror
</div>

<div style="margin-bottom:16px;">
    <label for="email">Email <span aria-hidden="true">*</span></label>
    <input id="email" type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required maxlength="255">
    @error('email')<div class="error">{{ $message }}</div>@enderror
</div>

<div style="margin-bottom:16px;">
    <label for="role">Role <span aria-hidden="true">*</span></label>
    <select id="role" name="role" required>
        @foreach ([\App\Models\User::ROLE_PETUGAS, \App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_DIREKSI] as $role)
            <option value="{{ $role }}" @selected(old('role', $user->role ?? '') === $role)>{{ $role }}</option>
        @endforeach
    </select>
    @error('role')<div class="error">{{ $message }}</div>@enderror
</div>

<div style="margin-bottom:16px;">
    <label for="password">Password @if (isset($user)) <span class="muted">(kosongkan jika tidak diubah)</span> @else <span aria-hidden="true">*</span> @endif</label>
    <input id="password" type="password" name="password" @if (! isset($user)) required @endif minlength="8" autocomplete="new-password">
    @error('password')<div class="error">{{ $message }}</div>@enderror
</div>

<div style="display:flex; gap:8px;">
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    <a href="{{ route('admin.users.index') }}" class="btn btn-light">Batal</a>
</div>
