<div class="modal fade" id="uninstall-modal-{{ $module['slug'] }}" tabindex="-1" aria-labelledby="uninstall-modal-label-{{ $module['slug'] }}" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.modules.uninstall', $module['name']) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="uninstall-modal-label-{{ $module['slug'] }}">Uninstall {{ $module['name'] }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger small">
                    This will permanently delete module tables and permissions. This action cannot be undone.
                </div>
                <p class="mb-2">Type <strong>{{ $module['name'] }}</strong> to confirm:</p>
                <input type="text" class="form-control" name="module_name" required autocomplete="off">
                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" value="1" id="uninstall-confirm-{{ $module['slug'] }}" name="confirm" required>
                    <label class="form-check-label" for="uninstall-confirm-{{ $module['slug'] }}">
                        I understand this will delete all module data.
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Confirm Uninstall</button>
            </div>
        </form>
    </div>
</div>
