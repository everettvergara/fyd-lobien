document.addEventListener('DOMContentLoaded', () => {
    const modalElement = document.getElementById('mediaPickerModal');
    if (!modalElement) {
        return;
    }

    const modal = new bootstrap.Modal(modalElement);
    const grid = document.getElementById('mediaPickerGrid');
    const emptyState = document.getElementById('mediaPickerEmpty');
    const searchInput = document.getElementById('mediaPickerSearch');
    const searchBtn = document.getElementById('mediaPickerSearchBtn');
    const pickerUrl = document.body.dataset.mediaPickerUrl;
    const uploadUrl = document.body.dataset.mediaUploadUrl;
    const uploadForm = document.getElementById('mediaPickerUploadForm');
    const uploadFile = document.getElementById('mediaPickerUploadFile');
    const uploadBtn = document.getElementById('mediaPickerUploadBtn');
    const uploadError = document.getElementById('mediaPickerUploadError');
    const uploadHelp = document.getElementById('mediaPickerUploadHelp');
    const modalTitle = document.getElementById('mediaPickerModalLabel');
    const multiFooter = document.getElementById('mediaPickerMultiFooter');
    const selectionCount = document.getElementById('mediaPickerSelectionCount');
    const confirmBtn = document.getElementById('mediaPickerConfirmBtn');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    if (!pickerUrl) {
        return;
    }

    let activePicker = null;
    let pickerMode = 'single';
    let pickerType = 'image';
    let multiCallback = null;
    let selectedItems = new Map();

    const itemPreviewUrl = (item) => item.thumbnail_url || item.thumbnailUrl || item.url || item.preview_url;
    const itemFullUrl = (item) => item.preview_url || item.url || item.thumbnail_url || item.thumbnailUrl;
    const itemAlt = (item) => item.alt_text || item.alt || item.title || item.filename || 'Selected media';
    const itemFilename = (item) => item.filename || item.title || itemAlt(item);
    const isPdfItem = (item) => item.type === 'pdf' || item.mime_type === 'application/pdf' || pickerType === 'pdf';

    const renderPreviewHtml = (item) => {
        if (isPdfItem(item)) {
            return `<span class="text-muted small px-2 text-center media-picker-file"><i class="bi bi-file-earmark-pdf fs-2 d-block mb-1" aria-hidden="true"></i>${itemFilename(item)}</span>`;
        }

        const url = itemFullUrl(item);

        return `<img src="${url}" alt="${itemAlt(item)}" class="img-fluid media-picker-image">`;
    };

    const renderChoiceHtml = (item) => {
        if (isPdfItem(item)) {
            return `
                <button type="button" class="btn p-0 border w-100 media-picker-choice d-flex flex-column align-items-center justify-content-center" data-id="${item.id}" data-url="${itemFullUrl(item)}" data-alt="${itemAlt(item)}" data-filename="${itemFilename(item)}" style="height:100px;">
                    <i class="bi bi-file-earmark-pdf fs-2 text-danger" aria-hidden="true"></i>
                    <span class="small text-truncate w-100 px-1">${itemFilename(item)}</span>
                </button>`;
        }

        return `
                <button type="button" class="btn p-0 border w-100 media-picker-choice" data-id="${item.id}" data-url="${itemFullUrl(item)}" data-alt="${itemAlt(item)}">
                    <img src="${itemPreviewUrl(item)}" alt="${itemAlt(item)}" class="img-fluid rounded" style="height:100px;width:100%;object-fit:cover;">
                </button>`;
    };

    const appendUploadFiles = (formData, files) => {
        const fileList = Array.from(files);

        if (fileList.length === 1) {
            formData.append('file', fileList[0]);

            return;
        }

        fileList.forEach((file, index) => {
            formData.append(`files[${index}]`, file);
        });
    };

    const updateMultiUi = () => {
        const count = selectedItems.size;
        selectionCount.textContent = `${count} selected`;
        confirmBtn.disabled = count === 0;
        confirmBtn.textContent = count === 1 ? 'Insert 1 image' : `Insert ${count} images`;

        grid.querySelectorAll('.media-picker-choice').forEach((choice) => {
            const selected = selectedItems.has(choice.dataset.id);
            choice.classList.toggle('border-primary', selected);
            choice.classList.toggle('border-3', selected);
        });
    };

    const finishSingle = (item) => {
        if (!activePicker || !item?.id) {
            return;
        }

        const input = activePicker.querySelector('.media-picker-input');
        const preview = activePicker.querySelector('.media-picker-preview');
        const clearBtn = activePicker.querySelector('.media-picker-clear');

        input.value = item.id;
        preview.innerHTML = renderPreviewHtml(item);
        clearBtn.classList.remove('d-none');
        modal.hide();
    };

    const finishMulti = () => {
        if (!multiCallback || selectedItems.size === 0) {
            return;
        }

        multiCallback(Array.from(selectedItems.values()));
        modal.hide();
    };

    const finishMultiForPicker = (items) => {
        if (!activePicker || !items?.length) {
            return;
        }

        const grid = activePicker.querySelector('.media-picker-grid');
        const name = activePicker.dataset.pickerName;
        const clearBtn = activePicker.querySelector('.media-picker-clear');

        if (!grid || !name) {
            return;
        }

        grid.innerHTML = '';

        items.forEach((item) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'media-picker-item position-relative border rounded bg-light';
            wrapper.style.cssText = 'width:80px;height:80px;overflow:hidden;';
            wrapper.innerHTML = `
                <img src="${item.url}" alt="${itemAlt(item)}" class="img-fluid" style="width:100%;height:100%;object-fit:cover;">
                <button type="button" class="btn btn-sm btn-danger media-picker-remove-item position-absolute top-0 end-0" style="line-height:1;padding:0 4px;" aria-label="Remove image">&times;</button>
                <input type="hidden" name="${name}[]" value="${item.id}" class="media-picker-input">
            `;
            grid.appendChild(wrapper);
        });

        clearBtn?.classList.remove('d-none');
        modal.hide();
    };

    const configureModal = (mode, type = 'image') => {
        pickerMode = mode;
        pickerType = type;
        const isMulti = mode === 'multi';
        const isPdf = type === 'pdf';

        modalTitle.textContent = isMulti
            ? (isPdf ? 'Select PDFs' : 'Select Images')
            : (isPdf ? 'Select PDF' : 'Select Image');
        multiFooter?.classList.toggle('d-none', !isMulti);
        uploadBtn.textContent = isMulti ? 'Upload & Add' : 'Upload';
        uploadHelp.textContent = isMulti
            ? (isPdf
                ? 'Select one or more PDFs to upload, then choose from the library or insert selected files into the editor.'
                : 'Select one or more images to upload, then choose from the library or insert selected images into the editor.')
            : (isPdf
                ? 'Select one or more PDFs to upload. A single PDF is chosen for this field; multiple uploads are added to the library.'
                : 'Select one or more images to upload. A single image is chosen for this field; multiple uploads are added to the library.');

        selectedItems = new Map();
        updateMultiUi();
    };

    const toggleMultiItem = (item) => {
        if (selectedItems.has(String(item.id))) {
            selectedItems.delete(String(item.id));
        } else {
            selectedItems.set(String(item.id), {
                id: item.id,
                url: itemFullUrl(item),
                alt: itemAlt(item),
            });
        }

        updateMultiUi();
    };

    const handleUploadedItems = async (items) => {
        if (items.length === 0) {
            return;
        }

        if (pickerMode === 'multi') {
            items.forEach((item) => {
                selectedItems.set(String(item.id), {
                    id: item.id,
                    url: itemFullUrl(item),
                    alt: itemAlt(item),
                });
            });
            updateMultiUi();
            await loadImages(searchInput.value.trim());

            return;
        }

        await loadImages(searchInput.value.trim());

        if (items.length === 1 && activePicker) {
            finishSingle(items[0]);
        }
    };

    const loadImages = async (search = '') => {
        const url = new URL(pickerUrl, window.location.origin);
        if (search) {
            url.searchParams.set('search', search);
        }
        if (pickerType) {
            url.searchParams.set('type', pickerType);
        }

        const response = await fetch(url.toString(), {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (!response.ok) {
            return;
        }

        const data = await response.json();
        grid.innerHTML = '';

        if (!data.items?.length) {
            emptyState.classList.remove('d-none');
            return;
        }

        emptyState.classList.add('d-none');

        data.items.forEach((item) => {
            const col = document.createElement('div');
            col.className = 'col-md-3 col-sm-4 col-6';
            col.innerHTML = renderChoiceHtml(item);
            grid.appendChild(col);
        });

        updateMultiUi();
    };

    window.openMediaPicker = ({ mode = 'single', type = 'image', onSelect = null } = {}) => {
        activePicker = null;
        multiCallback = onSelect;
        configureModal(mode, type);
        searchInput.value = '';
        uploadForm?.reset();
        uploadError?.classList.add('d-none');
        loadImages();
        modal.show();
    };

    document.querySelectorAll('.media-picker-open').forEach((button) => {
        button.addEventListener('click', () => {
            activePicker = button.closest('.media-picker');
            multiCallback = null;
            configureModal(activePicker.dataset.pickerMode || 'single', activePicker.dataset.pickerType || 'image');
            searchInput.value = '';
            uploadForm?.reset();
            uploadError?.classList.add('d-none');

            if (activePicker.dataset.pickerMode === 'multi') {
                activePicker.querySelectorAll('.media-picker-input').forEach((input) => {
                    const item = input.closest('.media-picker-item');
                    const image = item?.querySelector('img');

                    if (!input.value) {
                        return;
                    }

                    selectedItems.set(String(input.value), {
                        id: input.value,
                        url: image?.src || '',
                        alt: image?.alt || 'Selected media',
                    });
                });
                updateMultiUi();
            }

            loadImages();
            modal.show();
        });
    });

    grid.addEventListener('click', (event) => {
        const choice = event.target.closest('.media-picker-choice');
        if (!choice) {
            return;
        }

        const item = {
            id: choice.dataset.id,
            url: choice.dataset.url,
            alt: choice.dataset.alt,
            filename: choice.dataset.filename,
            type: pickerType,
            mime_type: pickerType === 'pdf' ? 'application/pdf' : null,
        };

        if (pickerMode === 'multi') {
            toggleMultiItem(item);
            return;
        }

        if (!activePicker) {
            return;
        }

        finishSingle(item);
    });

    confirmBtn?.addEventListener('click', () => {
        if (activePicker?.dataset.pickerMode === 'multi') {
            finishMultiForPicker(Array.from(selectedItems.values()));

            return;
        }

        finishMulti();
    });

    uploadForm?.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!uploadUrl || !uploadFile?.files?.length) {
            uploadError.textContent = 'Choose one or more images to upload.';
            uploadError.classList.remove('d-none');
            return;
        }

        uploadError?.classList.add('d-none');
        uploadBtn.disabled = true;
        uploadBtn.textContent = 'Uploading...';

        const formData = new FormData();
        appendUploadFiles(formData, uploadFile.files);

        try {
            const response = await fetch(uploadUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
                },
                body: formData,
            });

            const data = await response.json();

            if (!response.ok) {
                const firstError = data.errors ? Object.values(data.errors).flat()[0] : null;
                throw new Error(firstError || data.message || 'Upload failed.');
            }

            uploadForm.reset();
            await handleUploadedItems(data.items ?? []);
        } catch (error) {
            uploadError.textContent = error.message || 'Upload failed.';
            uploadError.classList.remove('d-none');
        } finally {
            uploadBtn.disabled = false;
            uploadBtn.textContent = pickerMode === 'multi' ? 'Upload & Add' : 'Upload';
        }
    });

    document.querySelectorAll('.media-picker-clear').forEach((button) => {
        button.addEventListener('click', () => {
            const picker = button.closest('.media-picker');

            if (picker.dataset.pickerMode === 'multi') {
                const grid = picker.querySelector('.media-picker-grid');
                if (grid) {
                    grid.innerHTML = '';
                }
                button.classList.add('d-none');

                return;
            }

            picker.querySelector('.media-picker-input').value = '';
            const placeholder = picker.dataset.pickerType === 'pdf' ? 'No PDF selected' : 'No image selected';
            picker.querySelector('.media-picker-preview').innerHTML = `<span class="text-muted small px-2 text-center media-picker-placeholder">${placeholder}</span>`;
            button.classList.add('d-none');
        });
    });

    document.addEventListener('click', (event) => {
        const removeBtn = event.target.closest('.media-picker-remove-item');
        if (!removeBtn) {
            return;
        }

        const item = removeBtn.closest('.media-picker-item');
        const picker = removeBtn.closest('.media-picker');
        item?.remove();

        if (picker && !picker.querySelector('.media-picker-input')) {
            picker.querySelector('.media-picker-clear')?.classList.add('d-none');
        }
    });

    searchBtn.addEventListener('click', () => loadImages(searchInput.value.trim()));
    searchInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            loadImages(searchInput.value.trim());
        }
    });
});
