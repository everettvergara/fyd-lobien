<div class="mb-3">
    <label for="name" class="form-label">Full Name</label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
           value="{{ old('name', $user?->name) }}" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="email" class="form-label">Email Address</label>
    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
           value="{{ old('email', $user?->email) }}" required>
    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="password" class="form-label">{{ $user ? 'New Password (leave blank to keep current)' : 'Password' }}</label>
    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password"
           {{ $user ? '' : 'required' }}>
    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

@if (! $user)
    <div class="mb-3">
        <label for="password_confirmation" class="form-label">Confirm Password</label>
        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
    </div>
@endif

<div class="mb-3">
    <label for="status" class="form-label">Status</label>
    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
        @foreach ($statuses as $status)
            <option value="{{ $status->value }}" {{ old('status', $user?->status?->value) === $status->value ? 'selected' : '' }}>
                {{ $status->label() }}
            </option>
        @endforeach
    </select>
    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-4">
    <label class="form-label">Roles</label>
    @foreach ($roles as $role)
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}"
                   {{ in_array($role->id, old('roles', $user?->roles?->pluck('id')->toArray() ?? [])) ? 'checked' : '' }}>
            <label class="form-check-label" for="role_{{ $role->id }}">
                {{ $role->display_name }}
                @if ($role->description)
                    <span class="text-muted small">— {{ $role->description }}</span>
                @endif
            </label>
        </div>
    @endforeach
    @error('roles')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
</div>
