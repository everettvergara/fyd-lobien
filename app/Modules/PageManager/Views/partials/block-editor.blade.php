@php
    $inputPrefix = $inputPrefix ?? 'blocks';
    $initialBlocks = $initialBlocks ?? [];
@endphp

<div class="page-block-editor border rounded p-3" data-block-editor data-input-prefix="{{ $inputPrefix }}">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="h6 mb-0">Region Layout</h2>
        <span class="text-muted small">Drag blocks from the left into a region. Reorder rows using the grip handle.</span>
    </div>

    <div class="page-block-editor-layout">
        <aside class="page-block-palette-pane">
            <div class="page-block-palette-header">Available blocks</div>
            <div class="block-palette">
                @foreach ($blockPalette as $block)
                    <div
                        class="block-palette-item"
                        data-block-type="{{ $block['key'] }}"
                        data-block-label="{{ $block['label'] ?? $block['key'] }}"
                        title="{{ ($block['module'] ?? '') !== '' ? ($block['module'] ?? '').' · '.($block['label'] ?? $block['key']) : ($block['label'] ?? $block['key']) }}"
                    >
                        <i class="bi {{ $block['icon'] ?? 'bi-box' }} me-1"></i>{{ $block['label'] ?? $block['key'] }}
                    </div>
                @endforeach
            </div>
        </aside>

        <div class="page-block-regions">
            @foreach ($regions as $region)
                <div class="region-dropzone" data-region-key="{{ $region['key'] }}">
                    <div class="region-dropzone-header">
                        <strong>{{ $region['label'] ?? $region['key'] }}</strong>
                        @if (! empty($region['description']))
                            <span class="text-muted">{{ $region['description'] }}</span>
                        @endif
                    </div>
                    <div class="region-blocks" data-region-blocks="{{ $region['key'] }}">
                        <p class="text-muted small empty-region mb-0">Drop blocks here</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div data-block-inputs></div>

    <script type="application/json" data-initial-blocks>@json($initialBlocks)</script>
    <script type="application/json" data-block-palette-json>@json($blockPalette)</script>
</div>

@pushOnce('scripts', 'page-block-editor')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-block-editor]').forEach((editor) => {
                const prefix = editor.dataset.inputPrefix || 'blocks';
                const inputsHost = editor.querySelector('[data-block-inputs]');
                const initial = JSON.parse(editor.querySelector('[data-initial-blocks]')?.textContent || '[]');
                const palette = JSON.parse(editor.querySelector('[data-block-palette-json]')?.textContent || '[]');
                const paletteByType = Object.fromEntries(palette.map((block) => [block.key, block]));
                const state = [];
                let draggedRow = null;
                let pointerDrag = null;

                editor.querySelectorAll('.block-palette-item[data-block-type]').forEach((item) => {
                    item.addEventListener('pointerdown', (event) => {
                        if (event.button !== 0) {
                            return;
                        }

                        startPointerDrag(event, {
                            type: 'palette',
                            blockType: item.dataset.blockType,
                            label: item.dataset.blockLabel || item.dataset.blockType,
                        }, item);
                    });

                    item.addEventListener('dragstart', (event) => {
                        draggedRow = null;
                        event.dataTransfer.effectAllowed = 'copy';
                        event.dataTransfer.setData('text/plain', item.dataset.blockType);
                        event.dataTransfer.setData('block/type', item.dataset.blockType);
                        event.dataTransfer.setData('block/label', item.dataset.blockLabel || item.dataset.blockType);
                    });
                });

                function bindRowDrag(source, row) {
                    source.addEventListener('pointerdown', (event) => {
                        if (event.button !== 0) {
                            return;
                        }

                        startPointerDrag(event, {
                            type: 'row',
                            row,
                        }, source);
                    });

                    source.addEventListener('dragstart', (event) => {
                        draggedRow = row;
                        row.classList.add('is-dragging');
                        event.dataTransfer.effectAllowed = 'move';
                        event.dataTransfer.setData('text/plain', 'row');
                    });
                }

                function startPointerDrag(event, payload, source) {
                    event.preventDefault();

                    const ghost = source.cloneNode(true);
                    ghost.classList.add('page-block-drag-ghost');
                    ghost.style.width = `${source.offsetWidth}px`;
                    document.body.appendChild(ghost);

                    pointerDrag = {
                        payload,
                        source,
                        ghost,
                        pointerId: event.pointerId,
                    };

                    if (payload.type === 'row') {
                        payload.row.classList.add('is-dragging');
                    }

                    try {
                        source.setPointerCapture?.(event.pointerId);
                    } catch (e) {
                        // Pointer capture is a nicety; dragging works without it.
                    }

                    movePointerGhost(event);
                    window.addEventListener('pointermove', handlePointerMove);
                    window.addEventListener('pointerup', handlePointerUp, { once: true });
                    window.addEventListener('pointercancel', cancelPointerDrag, { once: true });
                }

                function movePointerGhost(event) {
                    if (!pointerDrag) {
                        return;
                    }

                    pointerDrag.ghost.style.transform = `translate(${event.clientX + 12}px, ${event.clientY + 12}px)`;
                }

                function handlePointerMove(event) {
                    if (!pointerDrag || event.pointerId !== pointerDrag.pointerId) {
                        return;
                    }

                    event.preventDefault();
                    movePointerGhost(event);
                    editor.querySelectorAll('.region-dropzone.is-drag-over').forEach((dropzone) => {
                        dropzone.classList.remove('is-drag-over');
                    });

                    const target = document.elementFromPoint(event.clientX, event.clientY);
                    const zone = target?.closest?.('[data-region-blocks]');
                    zone?.closest('.region-dropzone')?.classList.add('is-drag-over');

                    if (pointerDrag.payload.type !== 'row' || !zone || !zone.contains(pointerDrag.payload.row)) {
                        return;
                    }

                    const targetRow = target.closest('.page-block-row');
                    if (!targetRow || targetRow === pointerDrag.payload.row) {
                        return;
                    }

                    const rect = targetRow.getBoundingClientRect();
                    const after = event.clientY > rect.top + rect.height / 2;
                    zone.insertBefore(pointerDrag.payload.row, after ? targetRow.nextSibling : targetRow);
                }

                function handlePointerUp(event) {
                    if (!pointerDrag || event.pointerId !== pointerDrag.pointerId) {
                        return;
                    }

                    const payload = pointerDrag.payload;
                    const target = document.elementFromPoint(event.clientX, event.clientY);
                    const zone = target?.closest?.('[data-region-blocks]');

                    if (payload.type === 'palette' && zone) {
                        addBlock(zone.dataset.regionBlocks, payload.blockType, payload.label);
                    } else if (payload.type === 'row') {
                        const rowZone = payload.row.closest('[data-region-blocks]');
                        if (rowZone) {
                            updateRegionSortOrders(rowZone);
                        }
                    }

                    cleanupPointerDrag();
                }

                function cancelPointerDrag() {
                    cleanupPointerDrag();
                    render();
                }

                function cleanupPointerDrag() {
                    if (!pointerDrag) {
                        return;
                    }

                    pointerDrag.payload.row?.classList.remove('is-dragging');
                    pointerDrag.ghost.remove();
                    editor.querySelectorAll('.region-dropzone.is-drag-over').forEach((dropzone) => {
                        dropzone.classList.remove('is-drag-over');
                    });
                    window.removeEventListener('pointermove', handlePointerMove);
                    pointerDrag = null;
                }

                editor.addEventListener('dragend', () => {
                    if (!draggedRow) {
                        return;
                    }

                    draggedRow.classList.remove('is-dragging');
                    const zone = draggedRow.closest('[data-region-blocks]');
                    if (zone) {
                        updateRegionSortOrders(zone);
                    }
                    draggedRow = null;
                });

                editor.querySelectorAll('[data-region-blocks]').forEach((zone) => {
                    const dropzone = zone.closest('.region-dropzone');

                    zone.addEventListener('dragover', (event) => {
                        event.preventDefault();

                        if (draggedRow && zone.contains(draggedRow)) {
                            const targetRow = event.target.closest('.page-block-row');
                            if (!targetRow || targetRow === draggedRow) {
                                return;
                            }

                            const rect = targetRow.getBoundingClientRect();
                            const after = event.clientY > rect.top + rect.height / 2;
                            zone.insertBefore(draggedRow, after ? targetRow.nextSibling : targetRow);
                            return;
                        }

                        dropzone?.classList.add('is-drag-over');
                    });

                    zone.addEventListener('dragleave', (event) => {
                        if (!zone.contains(event.relatedTarget)) {
                            dropzone?.classList.remove('is-drag-over');
                        }
                    });

                    zone.addEventListener('drop', (event) => {
                        event.preventDefault();
                        dropzone?.classList.remove('is-drag-over');

                        if (draggedRow) {
                            return;
                        }

                        const type = event.dataTransfer.getData('block/type')
                            || event.dataTransfer.getData('text/plain');
                        const label = event.dataTransfer.getData('block/label') || type;
                        if (!type || type === 'row') {
                            return;
                        }

                        addBlock(zone.dataset.regionBlocks, type, label);
                    });
                });

                function schemaFor(blockType) {
                    return paletteByType[blockType]?.config_schema || [];
                }

                function defaultConfig(blockType) {
                    const config = {};

                    schemaFor(blockType).forEach((field) => {
                        if (field.key && Object.prototype.hasOwnProperty.call(field, 'default')) {
                            config[field.key] = field.default;
                        }
                    });

                    return config;
                }

                function normalizeConfigValue(field, value) {
                    if (field.type === 'number') {
                        const parsed = Number(value);
                        return Number.isFinite(parsed) ? parsed : '';
                    }

                    return value ?? '';
                }

                function setConfigValue(block, field, value) {
                    if (!block.config) block.config = {};
                    const normalized = normalizeConfigValue(field, value);

                    if (normalized === '' || normalized === null) {
                        delete block.config[field.key];
                    } else {
                        block.config[field.key] = normalized;
                    }
                }

                function createFieldInput(field, block) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'page-block-field';

                    const label = document.createElement('label');
                    label.className = 'page-block-field-label';
                    label.textContent = field.label || field.key;
                    if (field.required) {
                        label.innerHTML += '<span class="text-danger">*</span>';
                    }
                    wrapper.appendChild(label);

                    let input;
                    const currentValue = block.config?.[field.key] ?? field.default ?? '';

                    if (field.type === 'select') {
                        input = document.createElement('select');
                        input.className = 'form-select form-select-sm';
                        const placeholder = document.createElement('option');
                        placeholder.value = '';
                        placeholder.textContent = field.required ? 'Select…' : 'None';
                        input.appendChild(placeholder);
                        (field.options || []).forEach((option) => {
                            const opt = document.createElement('option');
                            opt.value = option.value;
                            opt.textContent = option.label;
                            input.appendChild(opt);
                        });
                        input.value = currentValue === undefined || currentValue === null ? '' : String(currentValue);
                    } else if (field.type === 'textarea') {
                        input = document.createElement('textarea');
                        input.className = 'form-control form-control-sm';
                        input.rows = 1;
                        input.value = currentValue ?? '';
                    } else if (field.type === 'number') {
                        input = document.createElement('input');
                        input.type = 'number';
                        input.className = 'form-control form-control-sm';
                        if (field.min !== undefined) input.min = field.min;
                        if (field.max !== undefined) input.max = field.max;
                        input.value = currentValue ?? '';
                    } else {
                        input = document.createElement('input');
                        input.type = 'text';
                        input.className = 'form-control form-control-sm';
                        input.value = currentValue ?? '';
                    }

                    input.title = field.help || '';
                    input.addEventListener('change', () => {
                        setConfigValue(block, field, input.value);
                        syncInputs();
                    });

                    input.addEventListener('input', () => {
                        if (field.type === 'text' || field.type === 'textarea' || field.type === 'number') {
                            setConfigValue(block, field, input.value);
                            syncInputs();
                        }
                    });

                    wrapper.appendChild(input);

                    return wrapper;
                }

                function render() {
                    editor.querySelectorAll('[data-region-blocks]').forEach((zone) => {
                        zone.innerHTML = '';
                        const regionKey = zone.dataset.regionBlocks;
                        const regionBlocks = state.filter((block) => block.region_key === regionKey);

                        if (regionBlocks.length === 0) {
                            zone.innerHTML = '<p class="text-muted small empty-region mb-0">Drop blocks here</p>';
                            return;
                        }

                        regionBlocks.forEach((block) => {
                            const row = document.createElement('div');
                            row.className = 'page-block-row';
                            row.dataset.stateIndex = String(state.indexOf(block));

                            const title = document.createElement('span');
                            title.className = 'page-block-row-title';

                            const handle = document.createElement('span');
                            handle.className = 'page-block-row-handle';
                            handle.setAttribute('aria-label', 'Drag to reorder');
                            handle.innerHTML = '<i class="bi bi-grip-vertical text-muted me-1"></i>';
                            title.appendChild(handle);

                            const label = document.createElement('strong');
                            label.textContent = block.label;
                            title.appendChild(label);
                            bindRowDrag(title, row);
                            row.appendChild(title);

                            const schema = schemaFor(block.block_type);

                            if (schema.length > 0) {
                                const fieldsHost = document.createElement('div');
                                fieldsHost.className = 'page-block-row-fields';
                                schema.forEach((field) => {
                                    fieldsHost.appendChild(createFieldInput(field, block));
                                });
                                row.appendChild(fieldsHost);
                            }

                            const removeBtn = document.createElement('button');
                            removeBtn.type = 'button';
                            removeBtn.className = 'btn btn-sm btn-outline-danger page-block-row-remove';
                            removeBtn.innerHTML = '&times;';
                            removeBtn.addEventListener('click', () => {
                                const idx = state.indexOf(block);
                                if (idx >= 0) state.splice(idx, 1);
                                render();
                                syncInputs();
                            });
                            row.appendChild(removeBtn);

                            zone.appendChild(row);
                        });
                    });

                    syncInputs();
                }

                function addBlock(regionKey, blockType, label, config) {
                    state.push({
                        region_key: regionKey,
                        block_type: blockType,
                        sort_order: state.filter((b) => b.region_key === regionKey).length,
                        config: config ?? defaultConfig(blockType),
                        label: label || paletteByType[blockType]?.label || blockType,
                    });
                    render();
                }

                function updateRegionSortOrders(zone) {
                    const regionKey = zone.dataset.regionBlocks;
                    const newRegionBlocks = Array.from(zone.querySelectorAll('.page-block-row'))
                        .map((row) => state[parseInt(row.dataset.stateIndex, 10)])
                        .filter(Boolean);

                    newRegionBlocks.forEach((block, index) => {
                        block.sort_order = index;
                    });

                    let queueIndex = 0;
                    const rebuilt = state.map((block) => {
                        if (block.region_key !== regionKey) {
                            return block;
                        }

                        return newRegionBlocks[queueIndex++];
                    });

                    state.length = 0;
                    state.push(...rebuilt);
                    render();
                }

                function syncInputs() {
                    inputsHost.innerHTML = '';

                    state.forEach((block, index) => {
                        ['region_key', 'block_type', 'sort_order'].forEach((field) => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = `${prefix}[${index}][${field}]`;
                            input.value = block[field];
                            inputsHost.appendChild(input);
                        });

                        Object.entries(block.config || {}).forEach(([key, value]) => {
                            if (value === '' || value === null || value === undefined) return;

                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = `${prefix}[${index}][config][${key}]`;
                            input.value = value;
                            inputsHost.appendChild(input);
                        });
                    });
                }

                initial.forEach((block) => {
                    const meta = paletteByType[block.block_type];

                    state.push({
                        region_key: block.region_key,
                        block_type: block.block_type,
                        sort_order: block.sort_order ?? state.filter((b) => b.region_key === block.region_key).length,
                        config: { ...defaultConfig(block.block_type), ...(block.config || {}) },
                        label: meta?.label || block.block_type,
                    });
                });

                render();
            });
        });
    </script>
@endPushOnce
