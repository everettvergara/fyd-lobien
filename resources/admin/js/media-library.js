document.addEventListener('DOMContentLoaded', () => {
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    const uploadForm = document.getElementById('mediaUploadForm');
    const queue = document.getElementById('mediaUploadQueue');
    const fileInput = uploadForm?.querySelector('input[type="file"]');
    const dropZone = document.getElementById('mediaDropZone');
    const submitButton = uploadForm?.querySelector('button[type="submit"], button:not([type])');

    const queueItems = new Map();
    let activeUploads = 0;
    let successfulUploads = 0;
    let batchUploadCount = 0;
    let pendingReload = false;
    let isUploading = false;

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    }[char]));

    const parseUploadError = (xhr) => {
        let message = xhr.status === 413
            ? 'The file is larger than the server allows.'
            : 'Upload failed.';

        try {
            const payload = JSON.parse(xhr.responseText);
            const firstFieldError = payload.errors
                ? Object.values(payload.errors).flat().find(Boolean)
                : null;

            message = firstFieldError || payload.message || message;
        } catch (_) {}

        return message;
    };

    const setRowStatus = (id, { progress = null, message = null, success = false, failed = false } = {}) => {
        const item = queueItems.get(id);
        if (!item) return;

        const bar = item.row.querySelector('.progress-bar');
        const status = item.row.querySelector('.media-upload-status');
        const retry = item.row.querySelector('.media-upload-retry');
        const cancel = item.row.querySelector('.media-upload-cancel');

        if (progress !== null && bar) {
            bar.style.width = `${progress}%`;
        }

        if (success && bar) {
            bar.style.width = '100%';
            bar.classList.add('bg-success');
            status?.classList.remove('text-danger');
            cancel?.classList.add('d-none');
            retry?.classList.add('d-none');
        }

        if (failed && bar) {
            bar.classList.add('bg-danger');
            status?.classList.add('text-danger');
            retry?.classList.remove('d-none');
        }

        if (message && status) {
            status.textContent = message;
        }
    };

    const buildUploadFormData = (files) => {
        const data = new FormData();
        const csrfField = uploadForm?.querySelector('[name="_token"]');

        if (token) {
            data.append('_token', token);
        } else if (csrfField?.value) {
            data.append('_token', csrfField.value);
        }

        ['folder_id', 'tags', 'alt_text', 'title', 'description', 'caption', 'copyright', 'credit'].forEach((name) => {
            const field = uploadForm?.elements[name];
            if (field?.value) {
                data.append(name, field.value);
            }
        });

        const fileField = files.length === 1 ? 'file' : 'files[]';
        files.forEach((file) => {
            data.append(fileField, file);
        });

        return data;
    };

    const finishUpload = () => {
        activeUploads = Math.max(0, activeUploads - 1);

        if (activeUploads > 0 || pendingReload) {
            return;
        }

        if (successfulUploads === 0) {
            window.showToast?.('Upload failed. Check the queue below for details.', 'error');
            isUploading = false;
            submitButton?.removeAttribute('disabled');
            return;
        }

        const failedUploads = batchUploadCount - successfulUploads;
        pendingReload = true;

        if (failedUploads > 0 && window.showToast) {
            window.showToast(
                `${successfulUploads} file(s) uploaded, ${failedUploads} failed. Check the queue below for details.`,
                'error',
            );
        } else if (window.showToast) {
            window.showToast(
                successfulUploads === 1
                    ? 'File uploaded successfully.'
                    : `${successfulUploads} files uploaded successfully.`,
            );
        }

        if (failedUploads > 0) {
            isUploading = false;
            submitButton?.removeAttribute('disabled');
            return;
        }

        if (fileInput) {
            fileInput.value = '';
        }

        window.setTimeout(() => window.location.reload(), 400);
    };

    const addQueueItem = (file) => {
        if (!queue) return null;

        queue.classList.remove('d-none');
        const id = `${file.name}-${file.size}-${Math.random().toString(36).slice(2)}`;
        const row = document.createElement('div');
        row.className = 'border rounded p-2 mb-2';
        row.dataset.queueId = id;
        row.innerHTML = `
            <div class="d-flex justify-content-between align-items-center gap-2">
                <div class="small text-truncate">${escapeHtml(file.name)}</div>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary media-upload-retry d-none">Retry</button>
                    <button type="button" class="btn btn-outline-danger media-upload-cancel">Cancel</button>
                </div>
            </div>
            <div class="progress mt-2" style="height:6px;">
                <div class="progress-bar" style="width:0%"></div>
            </div>
            <div class="small text-muted mt-1 media-upload-status">Queued</div>
        `;
        queue.appendChild(row);
        queueItems.set(id, { id, row, file, xhr: null });
        return id;
    };

    const uploadSingleFile = (file, rowId) => new Promise((resolve) => {
        const item = queueItems.get(rowId);
        if (!item || !uploadForm) {
            resolve(false);
            return;
        }

        setRowStatus(rowId, { message: 'Uploading...' });

        const xhr = new XMLHttpRequest();
        item.xhr = xhr;

        xhr.upload.addEventListener('progress', (event) => {
            if (!event.lengthComputable) return;
            const percent = Math.round((event.loaded / event.total) * 100);
            setRowStatus(rowId, { progress: percent });
        });

        xhr.addEventListener('load', () => {
            if (xhr.status >= 200 && xhr.status < 300) {
                setRowStatus(rowId, { message: 'Uploaded', success: true, progress: 100 });
                resolve(true);
                return;
            }

            const message = parseUploadError(xhr);
            setRowStatus(rowId, { message, failed: true });
            window.showToast?.(`${file.name}: ${message}`, 'error');
            resolve(false);
        });

        xhr.addEventListener('error', () => {
            setRowStatus(rowId, {
                message: 'Upload failed because the connection was interrupted.',
                failed: true,
            });
            resolve(false);
        });

        xhr.addEventListener('abort', () => {
            setRowStatus(rowId, { message: 'Upload cancelled.', failed: true });
            resolve(false);
        });

        xhr.open('POST', uploadForm.action);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        if (token) xhr.setRequestHeader('X-CSRF-TOKEN', token);
        xhr.send(buildUploadFormData([file]));
    });

    const uploadBatch = async (files) => {
        if (!uploadForm || !files.length) {
            window.showToast?.('Please choose at least one file to upload.', 'error');
            return;
        }

        if (isUploading) {
            window.showToast?.('Please wait for the current upload to finish.', 'error');
            return;
        }

        isUploading = true;
        submitButton?.setAttribute('disabled', 'disabled');
        successfulUploads = 0;
        batchUploadCount = files.length;
        pendingReload = false;
        activeUploads++;

        const rowIds = files.map((file) => addQueueItem(file)).filter(Boolean);

        try {
            for (let index = 0; index < files.length; index += 1) {
                const rowId = rowIds[index];
                if (!rowId) continue;

                const uploaded = await uploadSingleFile(files[index], rowId);
                if (uploaded) {
                    successfulUploads += 1;
                }
            }
        } finally {
            finishUpload();
        }
    };

    uploadForm?.addEventListener('submit', (event) => {
        const files = Array.from(fileInput?.files ?? []);

        if (!files.length) {
            event.preventDefault();
            window.showToast?.('Please choose at least one file to upload.', 'error');
            return;
        }

        if (isUploading) {
            event.preventDefault();
            window.showToast?.('Please wait for the current upload to finish.', 'error');
            return;
        }

        isUploading = true;
        submitButton?.setAttribute('disabled', 'disabled');
        submitButton?.classList.add('disabled');
        queue?.classList.add('d-none');
    });

    queue?.addEventListener('click', (event) => {
        const row = event.target.closest('[data-queue-id]');
        if (!row) return;

        const id = row.dataset.queueId;
        const item = queueItems.get(id);
        if (!item) return;

        if (event.target.closest('.media-upload-cancel')) {
            item.xhr?.abort();
        }

        if (event.target.closest('.media-upload-retry')) {
            uploadBatch([item.file]);
        }
    });

    ['dragenter', 'dragover'].forEach((eventName) => {
        dropZone?.addEventListener(eventName, (event) => {
            event.preventDefault();
            dropZone.classList.add('border-primary');
        });
    });

    ['dragleave', 'drop'].forEach((eventName) => {
        dropZone?.addEventListener(eventName, (event) => {
            event.preventDefault();
            dropZone.classList.remove('border-primary');
        });
    });

    dropZone?.addEventListener('drop', (event) => {
        if (!event.dataTransfer?.files?.length) return;
        if (!fileInput) return;

        fileInput.files = event.dataTransfer.files;
        uploadForm?.requestSubmit();
    });

    document.querySelector('.media-select-all')?.addEventListener('change', (event) => {
        document.querySelectorAll('.media-checkbox').forEach((checkbox) => {
            checkbox.checked = event.target.checked;
        });
    });

    document.querySelectorAll('[data-media-view]').forEach((link) => {
        link.addEventListener('click', () => {
            fetch(document.body.dataset.mediaPreferenceUrl || '/admin/media/preference', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(token ? { 'X-CSRF-TOKEN': token } : {}),
                },
                body: JSON.stringify({ key: 'view', value: { mode: link.dataset.mediaView } }),
            }).catch(() => {});
        });
    });

    const previewModalElement = document.getElementById('mediaPreviewModal');
    const previewModal = previewModalElement ? new bootstrap.Modal(previewModalElement) : null;
    const stage = document.getElementById('mediaPreviewStage');
    const metadata = document.getElementById('mediaPreviewMetadata');
    const variants = document.getElementById('mediaPreviewVariants');
    const usage = document.getElementById('mediaPreviewUsage');
    const history = document.getElementById('mediaPreviewHistory');
    const metadataForm = document.getElementById('mediaMetadataForm');

    const renderPreview = (item) => {
        if (!stage || !metadata || !variants || !usage || !history || !metadataForm) return;

        document.getElementById('mediaPreviewTitle').textContent = item.title || item.filename;
        stage.innerHTML = '';

        if (item.type === 'image') {
            stage.innerHTML = `<img src="${item.url}" alt="${escapeHtml(item.alt_text || item.filename)}" class="img-fluid">`;
        } else if (item.type === 'video') {
            stage.innerHTML = `<video src="${item.url}" controls class="w-100"></video>`;
        } else if (item.type === 'audio') {
            stage.innerHTML = `<audio src="${item.url}" controls class="w-100"></audio>`;
        } else if (item.type === 'pdf') {
            stage.innerHTML = `<iframe src="${item.url}" class="w-100 border-0" style="height:520px;"></iframe>`;
        } else {
            stage.innerHTML = `<div class="text-center"><i class="bi bi-file-earmark-fill fs-1 text-muted"></i><p class="mt-2">${escapeHtml(item.filename)}</p><a href="${item.url}" class="btn btn-sm btn-secondary">Open File</a></div>`;
        }

        metadata.innerHTML = [
            ['Filename', item.filename],
            ['MIME Type', item.mime_type],
            ['Size', `${Math.round((item.size || 0) / 1024)} KB`],
            ['Dimensions', item.width && item.height ? `${item.width} x ${item.height}` : 'N/A'],
            ['Uploaded By', item.uploaded_by || 'Unknown'],
            ['Usage Count', item.usage_count],
        ].map(([label, value]) => `<dt class="col-5">${escapeHtml(label)}</dt><dd class="col-7">${escapeHtml(value)}</dd>`).join('');

        variants.innerHTML = item.variants.map((variant) => `<a class="btn btn-sm btn-outline-secondary" href="${variant.url}" target="_blank">${escapeHtml(variant.variant)}</a>`).join('');
        usage.innerHTML = item.usages?.length
            ? item.usages.map((record) => `<div>${escapeHtml(record.module)}: ${escapeHtml(record.label || record.usable_type)} <span class="text-muted">(${escapeHtml(record.field)})</span></div>`).join('')
            : 'No usage recorded.';
        history.innerHTML = item.history?.length
            ? item.history.map((record) => `<div>${escapeHtml(record.created_at)}: ${escapeHtml(record.user)} ${escapeHtml(record.action)}</div>`).join('')
            : 'No history recorded.';
        metadataForm.action = `/admin/media/${item.id}`;
        metadataForm.elements.title.value = item.title || '';
        metadataForm.elements.description.value = item.description || '';
        metadataForm.elements.caption.value = item.caption || '';
        metadataForm.elements.alt_text.value = item.alt_text || '';
        metadataForm.elements.tags.value = (item.tags || []).join(', ');
    };

    document.addEventListener('click', async (event) => {
        const trigger = event.target.closest('.media-preview-trigger');
        if (!trigger) return;

        const response = await fetch(trigger.dataset.previewUrl, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!response.ok) return;

        renderPreview(await response.json());
        previewModal?.show();
    });
});
