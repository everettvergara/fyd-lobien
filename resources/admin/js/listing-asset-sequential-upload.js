const jsonMessage = async (response) => {
    try {
        const payload = await response.json();
        return payload.message || Object.values(payload.errors || {}).flat().join(' ') || 'Upload failed.';
    } catch (error) {
        return response.statusText || 'Upload failed.';
    }
};

const submitPreview = (form, batchKey) => {
    const previewForm = document.createElement('form');
    previewForm.method = 'POST';
    previewForm.action = form.dataset.stagePreviewUrl;
    previewForm.classList.add('d-none');

    const token = form.querySelector('[name="_token"]')?.value || '';
    previewForm.innerHTML = `
        <input type="hidden" name="_token" value="${token}">
        <input type="hidden" name="batch_key" value="${batchKey}">
    `;

    document.body.appendChild(previewForm);
    previewForm.submit();
};

const uploadFile = (form, batchKey, file, row) => new Promise((resolve) => {
    const token = form.querySelector('[name="_token"]')?.value || '';
    const status = row.querySelector('[data-asset-upload-status]');
    const formData = new FormData();
    formData.append('_token', token);
    formData.append('batch_key', batchKey);
    formData.append('file', file);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', form.dataset.stageFileUrl);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

    xhr.upload.addEventListener('progress', (event) => {
        if (event.lengthComputable && status) {
            status.textContent = `Uploading ${Math.round((event.loaded / event.total) * 100)}%`;
        }
    });

    xhr.addEventListener('load', () => {
        if (xhr.status >= 200 && xhr.status < 300) {
            status.textContent = 'Staged';
            row.classList.remove('list-group-item-warning');
            row.classList.add('list-group-item-success');
            resolve(true);
            return;
        }

        let message = xhr.statusText || 'Upload failed.';
        try {
            const payload = JSON.parse(xhr.responseText || '{}');
            message = payload.message || Object.values(payload.errors || {}).flat().join(' ') || message;
        } catch (error) {
            // Keep the HTTP status text when response is not JSON.
        }

        status.textContent = message;
        row.classList.remove('list-group-item-warning');
        row.classList.add('list-group-item-danger');
        resolve(false);
    });

    xhr.addEventListener('error', () => {
        status.textContent = 'Network error while uploading this file.';
        row.classList.remove('list-group-item-warning');
        row.classList.add('list-group-item-danger');
        resolve(false);
    });

    status.textContent = 'Uploading 0%';
    xhr.send(formData);
});

const validateStagedFile = async (form, batchKey, index, row) => {
    const status = row.querySelector('[data-asset-upload-status]');
    const formData = new FormData();
    formData.append('_token', form.querySelector('[name="_token"]')?.value || '');
    formData.append('batch_key', batchKey);
    formData.append('index', String(index));

    status.textContent = 'Validating';
    row.classList.remove('list-group-item-success', 'list-group-item-info', 'list-group-item-danger');
    row.classList.add('list-group-item-warning');

    const response = await fetch(form.dataset.stageValidateUrl, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData,
    });

    if (!response.ok) {
        throw new Error(await jsonMessage(response));
    }

    const payload = await response.json();
    row.classList.remove('list-group-item-warning', 'list-group-item-success', 'list-group-item-info', 'list-group-item-danger');

    if (payload.status === 'invalid') {
        status.textContent = payload.message || 'Invalid';
        row.classList.add('list-group-item-danger');
        return payload;
    }

    if (payload.status === 'skip') {
        status.textContent = payload.message || 'Skip';
        row.classList.add('list-group-item-warning');
        return payload;
    }

    status.textContent = payload.status === 'replace' ? 'Replace' : 'Attach';
    row.classList.add(payload.status === 'replace' ? 'list-group-item-info' : 'list-group-item-success');

    return payload;
};

const validateStagedFiles = async (form, batchKey, rows) => {
    for (let index = 0; index < rows.length; index++) {
        await validateStagedFile(form, batchKey, index, rows[index]);
    }
};

const updateConfirmProgress = (progress, payload) => {
    const bar = progress?.querySelector('[data-asset-confirm-progress-bar]');
    const status = progress?.querySelector('[data-asset-confirm-progress-status]');
    const percent = payload.percent || 0;
    const summary = payload.summary || {};

    if (bar) {
        bar.style.width = `${percent}%`;
        bar.textContent = `${percent}%`;
        bar.parentElement?.setAttribute('aria-valuenow', String(percent));
    }

    if (status) {
        const current = payload.current?.filename ? ` Current: ${payload.current.filename} (${payload.current.status}).` : '';
        status.textContent = `Processed ${payload.processed || 0} of ${payload.total || 0}.${current} Attached: ${summary.attached || 0}, replaced: ${summary.replaced || 0}, skipped: ${summary.skipped || 0}, failed: ${summary.failed || 0}.`;
    }
};

document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-asset-sequential-upload]');
    if (!form) {
        return;
    }

    const fileInput = form.querySelector('input[type="file"][name="files[]"]');
    const assetType = form.querySelector('[name="asset_type"]');
    const submitButton = form.querySelector('button[type="submit"]');
    const queue = form.querySelector('[data-asset-upload-queue]');
    const queueList = form.querySelector('[data-asset-upload-queue-list]');
    const previewButton = form.querySelector('[data-asset-upload-preview-staged]');
    let currentBatchKey = null;

    const renderQueue = (files) => {
        queue?.classList.remove('d-none');
        previewButton?.classList.add('d-none');
        queueList.innerHTML = '';

        files.forEach((file) => {
            const row = document.createElement('div');
            row.className = 'list-group-item list-group-item-warning d-flex justify-content-between gap-3';
            const name = document.createElement('span');
            name.className = 'text-break';
            name.textContent = file.name;

            const status = document.createElement('span');
            status.className = 'text-nowrap';
            status.dataset.assetUploadStatus = '';
            status.textContent = 'Queued';

            row.append(name, status);
            queueList.appendChild(row);
        });
    };

    form.addEventListener('submit', async (event) => {
        const files = Array.from(fileInput?.files || []);
        const isSingleZip = files.length === 1 && files[0].name.toLowerCase().endsWith('.zip');

        if (files.length === 0 || isSingleZip) {
            return;
        }

        event.preventDefault();

        if (!assetType?.value) {
            window.showToast?.('Choose the asset type for this batch.', 'error');
            return;
        }

        renderQueue(files);
        submitButton.disabled = true;
        submitButton.textContent = 'Uploading files...';

        const startData = new FormData();
        startData.append('_token', form.querySelector('[name="_token"]')?.value || '');
        startData.append('asset_type', assetType.value);

        let startResponse;
        try {
            startResponse = await fetch(form.dataset.stageStartUrl, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: startData,
            });
        } catch (error) {
            window.showToast?.('Could not start the upload batch. Check your connection and try again.', 'error');
            submitButton.disabled = false;
            submitButton.textContent = 'Preview Batch';
            return;
        }

        if (!startResponse.ok) {
            window.showToast?.(await jsonMessage(startResponse), 'error');
            submitButton.disabled = false;
            submitButton.textContent = 'Preview Batch';
            return;
        }

        const startPayload = await startResponse.json();
        currentBatchKey = startPayload.batch_key;
        let staged = 0;
        let failed = 0;
        const rows = Array.from(queueList.children);

        for (let index = 0; index < files.length; index++) {
            const ok = await uploadFile(form, currentBatchKey, files[index], rows[index]);
            ok ? staged++ : failed++;
        }

        submitButton.disabled = false;
        submitButton.textContent = 'Preview Batch';

        if (failed > 0) {
            window.showToast?.(`${staged} file(s) staged, ${failed} failed. Review the queue errors.`, 'error');
            if (staged > 0) {
                previewButton?.classList.remove('d-none');
            }
            return;
        }

        submitButton.disabled = true;
        submitButton.textContent = 'Validating files...';

        try {
            await validateStagedFiles(form, currentBatchKey, rows);
        } catch (error) {
            window.showToast?.(error.message || 'Validation status failed.', 'error');
            submitButton.disabled = false;
            submitButton.textContent = 'Preview Batch';
            previewButton?.classList.remove('d-none');
            return;
        }

        submitButton.disabled = false;
        submitButton.textContent = 'Preview Batch';
        submitPreview(form, currentBatchKey);
    });

    previewButton?.addEventListener('click', async () => {
        if (currentBatchKey) {
            const rows = Array.from(queueList.children).filter((row) => row.classList.contains('list-group-item-success'));
            previewButton.disabled = true;
            previewButton.textContent = 'Validating staged files...';

            try {
                await validateStagedFiles(form, currentBatchKey, rows);
            } catch (error) {
                window.showToast?.(error.message || 'Validation status failed.', 'error');
                previewButton.disabled = false;
                previewButton.textContent = 'Preview staged files';
                return;
            }

            submitPreview(form, currentBatchKey);
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-asset-confirm-upload]').forEach((form) => {
        const submitButton = form.querySelector('button[type="submit"]');
        const progress = form.querySelector('[data-asset-confirm-progress]');

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            submitButton.disabled = true;
            submitButton.textContent = 'Uploading...';
            progress?.classList.remove('d-none');

            let index = 0;
            let done = false;
            let lastPayload = null;

            while (!done) {
                const formData = new FormData();
                formData.append('_token', form.querySelector('[name="_token"]')?.value || '');
                formData.append('batch_key', form.querySelector('[name="batch_key"]')?.value || '');
                formData.append('index', String(index));

                let response;
                try {
                    response = await fetch(form.dataset.progressUrl, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: formData,
                    });
                } catch (error) {
                    window.showToast?.('Upload stopped because the server could not be reached.', 'error');
                    submitButton.disabled = false;
                    submitButton.textContent = 'Confirm Upload';
                    return;
                }

                if (!response.ok) {
                    window.showToast?.(await jsonMessage(response), 'error');
                    submitButton.disabled = false;
                    submitButton.textContent = 'Confirm Upload';
                    return;
                }

                lastPayload = await response.json();
                updateConfirmProgress(progress, lastPayload);
                done = Boolean(lastPayload.done);
                index = lastPayload.next_index || index + 1;
            }

            const summary = lastPayload?.summary || {};
            window.showToast?.(`Batch upload complete: ${summary.attached || 0} attached, ${summary.replaced || 0} replaced, ${summary.skipped || 0} skipped, ${summary.failed || 0} failed.`);

            window.setTimeout(() => {
                window.location.href = lastPayload?.redirect_url || form.action;
            }, 1200);
        });
    });
});
