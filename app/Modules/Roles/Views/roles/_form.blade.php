<div class="row">
    <div class="col-md-6">
        @if (! $role)
            <div class="mb-3">
                <label for="name" class="form-label">Role Name (slug)</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                       value="{{ old('name') }}" required placeholder="e.g. content_manager">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        @else
            <div class="mb-3">
                <label class="form-label">Role Name</label>
                <input type="text" class="form-control" value="{{ $role->name }}" disabled>
            </div>
        @endif

        <div class="mb-3">
            <label for="display_name" class="form-label">Display Name</label>
            <input type="text" class="form-control @error('display_name') is-invalid @enderror" id="display_name" name="display_name"
                   value="{{ old('display_name', $role?->display_name) }}" required>
            @error('display_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-4">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="2">{{ old('description', $role?->description) }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="mb-4">
    <label class="form-label">Permissions</label>
    @php $selected = old('permissions', $role?->permissions?->pluck('id')->toArray() ?? []); @endphp
    <div class="row">
        @foreach ($permissions as $module => $modulePermissions)
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-header bg-light py-2">
                        <strong class="small text-uppercase">{{ ucfirst($module) }}</strong>
                    </div>
                    <div class="card-body py-2">
                        @foreach ($modulePermissions as $permission)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]"
                                       value="{{ $permission->id }}" id="perm_{{ $permission->id }}"
                                       {{ in_array($permission->id, $selected) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="perm_{{ $permission->id }}">
                                    {{ $permission->display_name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @error('permissions')<div class="text-danger small">{{ $message }}</div>@enderror
</div>
