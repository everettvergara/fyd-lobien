<div class="modal fade" id="mediaPickerModal" tabindex="-1" aria-labelledby="mediaPickerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mediaPickerModalLabel">Select Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @can('create', \App\Models\Media::class)
                    <form class="border rounded bg-light p-3 mb-3" id="mediaPickerUploadForm">
                        <label class="form-label small text-muted" for="mediaPickerUploadFile">Upload from computer</label>
                        <div class="input-group input-group-sm">
                            <input type="file" class="form-control" id="mediaPickerUploadFile" name="files[]" accept="image/*" multiple>
                            <button type="submit" class="btn btn-primary" id="mediaPickerUploadBtn">Upload & Select</button>
                        </div>
                        <div class="form-text" id="mediaPickerUploadHelp">Select one or more images to upload to the Media Library.</div>
                        <div class="invalid-feedback d-block d-none" id="mediaPickerUploadError"></div>
                    </form>
                @endcan
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="mediaPickerSearch" placeholder="Search images...">
                    <button type="button" class="btn btn-outline-secondary" id="mediaPickerSearchBtn">Search</button>
                </div>
                <div class="row g-3" id="mediaPickerGrid"></div>
                <p class="text-muted small mb-0 d-none" id="mediaPickerEmpty">No images found.</p>
            </div>
            <div class="modal-footer d-none" id="mediaPickerMultiFooter">
                <span class="text-muted small me-auto" id="mediaPickerSelectionCount">0 selected</span>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="mediaPickerConfirmBtn" disabled>Insert selected</button>
            </div>
        </div>
    </div>
</div>
