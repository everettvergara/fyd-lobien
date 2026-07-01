<div class="modal fade" id="mediaPickerModal" tabindex="-1" aria-labelledby="mediaPickerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mediaPickerModalLabel">Select Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="mediaPickerSearch" placeholder="Search images...">
                    <button type="button" class="btn btn-outline-secondary" id="mediaPickerSearchBtn">Search</button>
                </div>
                <div class="row g-3" id="mediaPickerGrid"></div>
                <p class="text-muted small mb-0 d-none" id="mediaPickerEmpty">No images found.</p>
            </div>
        </div>
    </div>
</div>
