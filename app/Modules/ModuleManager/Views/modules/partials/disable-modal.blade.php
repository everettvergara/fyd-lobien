<div class="modal fade" id="disable-modal-{{ $module['slug'] }}" tabindex="-1" aria-labelledby="disable-modal-label-{{ $module['slug'] }}" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.modules.disable', $module['name']) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="disable-modal-label-{{ $module['slug'] }}">Disable {{ $module['name'] }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>The <strong>{{ $module['group'] }}</strong> sidebar group and routes will be hidden.</p>
                <p class="mb-0 small text-muted">Module data will be preserved. You can enable the module again later.</p>
                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" value="1" id="disable-confirm-{{ $module['slug'] }}" name="confirm" required>
                    <label class="form-check-label" for="disable-confirm-{{ $module['slug'] }}">
                        Confirm disable
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning">Confirm Disable</button>
            </div>
        </form>
    </div>
</div>
