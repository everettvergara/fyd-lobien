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

    if (!pickerUrl) {
        return;
    }

    let activePicker = null;

    const loadImages = async (search = '') => {
        const url = new URL(pickerUrl, window.location.origin);
        if (search) {
            url.searchParams.set('search', search);
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
            col.innerHTML = `
                <button type="button" class="btn p-0 border w-100 media-picker-choice" data-id="${item.id}" data-url="${item.url}" data-alt="${item.alt_text || item.filename}">
                    <img src="${item.url}" alt="${item.alt_text || item.filename}" class="img-fluid rounded" style="height:100px;width:100%;object-fit:cover;">
                </button>`;
            grid.appendChild(col);
        });
    };

    document.querySelectorAll('.media-picker-open').forEach((button) => {
        button.addEventListener('click', () => {
            activePicker = button.closest('.media-picker');
            searchInput.value = '';
            loadImages();
            modal.show();
        });
    });

    grid.addEventListener('click', (event) => {
        const choice = event.target.closest('.media-picker-choice');
        if (!choice || !activePicker) {
            return;
        }

        const input = activePicker.querySelector('.media-picker-input');
        const preview = activePicker.querySelector('.media-picker-preview');
        const clearBtn = activePicker.querySelector('.media-picker-clear');

        input.value = choice.dataset.id;
        preview.innerHTML = `<img src="${choice.dataset.url}" alt="${choice.dataset.alt}" class="img-fluid media-picker-image">`;
        clearBtn.classList.remove('d-none');
        modal.hide();
    });

    document.querySelectorAll('.media-picker-clear').forEach((button) => {
        button.addEventListener('click', () => {
            const picker = button.closest('.media-picker');
            picker.querySelector('.media-picker-input').value = '';
            picker.querySelector('.media-picker-preview').innerHTML = '<span class="text-muted small px-2 text-center media-picker-placeholder">No image selected</span>';
            button.classList.add('d-none');
        });
    });

    searchBtn.addEventListener('click', () => loadImages(searchInput.value.trim()));
    searchInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            loadImages(searchInput.value.trim());
        }
    });
});
