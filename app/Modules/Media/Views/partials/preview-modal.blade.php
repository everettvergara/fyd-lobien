<div class="modal fade" id="mediaPreviewModal" tabindex="-1" aria-labelledby="mediaPreviewTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mediaPreviewTitle">Media Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="border rounded bg-light d-flex align-items-center justify-content-center p-3" id="mediaPreviewStage" style="min-height:360px;"></div>
                    </div>
                    <div class="col-lg-4">
                        <h6>Metadata</h6>
                        <dl class="row small mb-3" id="mediaPreviewMetadata"></dl>
                        <h6>Variants</h6>
                        <div id="mediaPreviewVariants" class="d-flex flex-wrap gap-2 mb-3"></div>
                        <h6>Usage</h6>
                        <div id="mediaPreviewUsage" class="small mb-3 text-muted">No usage recorded.</div>
                        <h6>History</h6>
                        <div id="mediaPreviewHistory" class="small mb-3 text-muted">No history recorded.</div>
                        <h6>Edit Metadata</h6>
                        <form method="POST" id="mediaMetadataForm">
                            @csrf
                            @method('PATCH')
                            <div class="mb-2">
                                <label class="form-label">Title</label>
                                <input class="form-control" name="title">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="2"></textarea>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Caption</label>
                                <textarea class="form-control" name="caption" rows="2"></textarea>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Alt Text</label>
                                <input class="form-control" name="alt_text">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tags</label>
                                <input class="form-control" name="tags" placeholder="Comma-separated tags">
                            </div>
                            <button class="btn btn-primary">Save Metadata</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
