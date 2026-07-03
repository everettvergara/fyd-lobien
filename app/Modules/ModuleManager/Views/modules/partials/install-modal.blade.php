<div class="modal fade" id="install-modal-{{ $module['slug'] }}" tabindex="-1" aria-labelledby="install-modal-label-{{ $module['slug'] }}" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.modules.install', $module['name']) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="install-modal-label-{{ $module['slug'] }}">Install {{ $module['name'] }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>This will install the business module and create its database tables.</p>
                <ul class="small mb-3">
                    <li><strong>Group:</strong> {{ $module['group'] }}</li>
                    <li><strong>Version:</strong> {{ $module['version'] }}</li>
                </ul>
                @if (!empty($module['features']))
                    <p class="mb-1 fw-medium">Feature modules:</p>
                    <ul class="small">
                        @foreach ($module['features'] as $feature)
                            <li>{{ $feature['label'] ?? 'Feature' }}</li>
                        @endforeach
                    </ul>
                @endif
                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" value="1" id="install-confirm-{{ $module['slug'] }}" name="confirm" required>
                    <label class="form-check-label" for="install-confirm-{{ $module['slug'] }}">
                        I understand this will run migrations and register permissions.
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Confirm Install</button>
            </div>
        </form>
    </div>
</div>
