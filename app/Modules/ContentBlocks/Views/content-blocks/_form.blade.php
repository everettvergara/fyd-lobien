@php
    $contentBlock = $contentBlock ?? null;
    $initialPreview = $initialPreview ?? null;
    $previewRoute = $previewRoute ?? route('admin.content-blocks.preview');
    $registry = app(\App\Modules\ContentBlocks\Support\ContentBlockFieldRegistry::class);
    $selectedTypes = old('content_types', $contentBlock?->content_types ?? []);
    $fields = old('fields', $contentBlock?->fields ?? [[
        'field' => 'title',
        'label' => $registry->defaultLabel('title'),
        'class' => $registry->defaultClass('title'),
        'id' => $registry->defaultId(old('key', $contentBlock?->key ?? 'new-block'), 'title'),
        'sort_order' => 0,
    ]]);
    $filters = old('filters', $contentBlock?->filters ?? []);
    if ($filters === []) {
        $filters = [['field' => '', 'operator' => '', 'value' => '']];
    }
@endphp

<form method="POST"
      action="{{ $contentBlock ? route('admin.content-blocks.update', $contentBlock) : route('admin.content-blocks.store') }}"
      class="content-block-editor"
      data-preview-route="{{ $previewRoute }}"
      data-field-meta='@json($fieldMeta)'
      data-operators-by-type='@json($operatorsByFieldType)'
      data-operator-labels='@json($operatorLabels)'>
    @csrf
    @if ($contentBlock)
        @method('PUT')
    @endif

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card content-block-section content-block-section--general border rounded overflow-hidden">
                <div class="card-header content-block-section__header py-2 fw-bold text-dark border-0">
                    <span class="content-block-section__title">General</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $contentBlock?->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Key</label>
                            <input type="text" name="key" class="form-control" value="{{ old('key', $contentBlock?->key) }}" required @readonly($contentBlock !== null) pattern="^[a-z0-9]+(?:-[a-z0-9]+)*$">
                            <div class="form-text">Kebab-case identifier used in Page Manager and themes.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                @foreach (\App\Enums\ContentStatus::cases() as $status)
                                    <option value="{{ $status->value }}" @selected(old('status', $contentBlock?->status?->value ?? 'draft') === $status->value)>{{ $status->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Icon class</label>
                            <input type="text" name="icon" class="form-control" value="{{ old('icon', $contentBlock?->icon ?? \App\Modules\ContentBlocks\Database\Seeders\ContentBlockSeeder::MENU_ICON) }}" required placeholder="bi-view-stacked">
                            <div class="form-text">Bootstrap Icons class for admin list and Page Manager palette.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card content-block-section content-block-section--content-types mt-3 border rounded overflow-hidden">
                <div class="card-header content-block-section__header py-2 fw-bold text-dark border-0">
                    <span class="content-block-section__title">Content Types</span>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @foreach ($contentTypes as $typeKey => $typeLabel)
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="content_types[]" value="{{ $typeKey }}" id="type-{{ $typeKey }}" @checked(in_array($typeKey, $selectedTypes, true))>
                                    <label class="form-check-label" for="type-{{ $typeKey }}">{{ $typeLabel }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card content-block-section content-block-section--fields mt-3 border rounded overflow-hidden">
                <div class="card-header content-block-section__header py-2 fw-bold text-dark border-0 d-flex justify-content-between align-items-center">
                    <span class="content-block-section__title">Fields</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="add-field-row">Add Field</button>
                </div>
                <div class="card-body" id="field-rows">
                    @foreach ($fields as $index => $field)
                        @include('contentblocks::content-blocks._field-row', ['index' => $index, 'field' => $field, 'fieldOptions' => $fieldOptions])
                    @endforeach
                </div>
            </div>

            <div class="card content-block-section content-block-section--filters mt-3 border rounded overflow-hidden">
                <div class="card-header content-block-section__header py-2 fw-bold text-dark border-0 d-flex justify-content-between align-items-center">
                    <span class="content-block-section__title">Filters</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="add-filter-row">Add Filter</button>
                </div>
                <div class="card-body" id="filter-rows">
                    @foreach ($filters as $index => $filter)
                        @include('contentblocks::content-blocks._filter-row', ['index' => $index, 'filter' => $filter, 'fieldOptions' => $fieldOptions])
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card content-block-section content-block-section--sort-pager border rounded overflow-hidden">
                <div class="card-header content-block-section__header py-2 fw-bold text-dark border-0">
                    <span class="content-block-section__title">Sort &amp; Pager</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Sort Field</label>
                        <select name="sort_field" class="form-select" required>
                            @foreach ($sortOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('sort_field', $contentBlock?->sort_field ?? 'published_at') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sort Direction</label>
                        <select name="sort_direction" class="form-select" required>
                            <option value="asc" @selected(old('sort_direction', $contentBlock?->sort_direction ?? 'desc') === 'asc')>Ascending</option>
                            <option value="desc" @selected(old('sort_direction', $contentBlock?->sort_direction ?? 'desc') === 'desc')>Descending</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Items Per Page</label>
                        <input type="number" name="items_per_page" class="form-control" min="1" max="100" value="{{ old('items_per_page', $contentBlock?->items_per_page ?? 10) }}" required>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="pagination_enabled" value="1" id="pagination_enabled" @checked(old('pagination_enabled', $contentBlock?->pagination_enabled ?? false))>
                        <label class="form-check-label" for="pagination_enabled">Enable pagination on public site</label>
                    </div>
                </div>
            </div>

            <div class="card content-block-section content-block-section--formatter mt-3 border rounded overflow-hidden">
                <div class="card-header content-block-section__header py-2 fw-bold text-dark border-0">
                    <span class="content-block-section__title">Formatter</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Display Format</label>
                        <select name="formatter" class="form-select" required>
                            @foreach ($formatterOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('formatter', $contentBlock?->formatter?->value ?? 'unformatted') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Wrapper Class</label>
                        <input type="text" name="wrapper_class" class="form-control" value="{{ old('wrapper_class', $contentBlock?->wrapper_class) }}" placeholder="content-block content-block--example">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Wrapper ID</label>
                        <input type="text" name="wrapper_id" class="form-control" value="{{ old('wrapper_id', $contentBlock?->wrapper_id) }}" placeholder="content-block-example">
                    </div>
                    <div class="form-text">Field rows include default CSS class and id hooks for theme styling.</div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-3">{{ $contentBlock ? 'Save Content Block' : 'Create Content Block' }}</button>
        </div>
    </div>

    <div class="accordion content-block-preview-accordion mt-4" id="contentBlockPreviewPanels">
        <div class="accordion-item content-block-section content-block-section--preview border rounded overflow-hidden">
            <h2 class="accordion-header" id="previewRetrieveHeading">
                <button
                    class="accordion-button py-2 {{ empty($initialPreview) ? 'collapsed' : '' }}"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#previewRetrievePanel"
                    aria-expanded="{{ ! empty($initialPreview) ? 'true' : 'false' }}"
                    aria-controls="previewRetrievePanel"
                >
                    <span class="content-block-section__title">Preview / Retrieve</span>
                </button>
            </h2>
            <div
                id="previewRetrievePanel"
                class="accordion-collapse collapse {{ ! empty($initialPreview) ? 'show' : '' }}"
                aria-labelledby="previewRetrieveHeading"
            >
                <div class="accordion-body pt-3">
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" name="auto_update_preview" value="1" id="auto-update-preview">
                            <label class="form-check-label" for="auto-update-preview">Auto Update Preview on save</label>
                        </div>
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="show-sql-preview">
                            <label class="form-check-label" for="show-sql-preview">Show SQL preview</label>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="retrieve-preview">Retrieve</button>
                    </div>
                    <div id="preview-meta" class="small text-muted mb-2">
                        @if (! empty($initialPreview))
                            @include('contentblocks::content-blocks._preview-meta', ['meta' => $initialPreview['meta']])
                        @else
                            Configure the block and click Retrieve to run the query against published content.
                        @endif
                    </div>
                    <div id="preview-results" class="content-block-preview-panel">
                        @if (! empty($initialPreview))
                            {!! $initialPreview['html'] !!}
                        @endif
                    </div>
                    <div id="preview-pagination" class="mt-2"></div>
                </div>
            </div>
        </div>

        <div class="accordion-item content-block-section content-block-section--generated-sql border rounded overflow-hidden mt-3 d-none" id="preview-sql-section">
            <h2 class="accordion-header" id="previewSqlHeading">
                <button
                    class="accordion-button py-2 collapsed"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#previewSqlPanel"
                    aria-expanded="false"
                    aria-controls="previewSqlPanel"
                >
                    <span class="content-block-section__title">Generated SQL</span>
                </button>
            </h2>
            <div
                id="previewSqlPanel"
                class="accordion-collapse collapse"
                aria-labelledby="previewSqlHeading"
            >
                <div class="accordion-body pt-3">
                    <div id="preview-sql">
                        @if (! empty($initialPreview['sql'] ?? null))
                            @include('contentblocks::content-blocks._preview-sql', ['sql' => $initialPreview['sql']])
                        @else
                            @include('contentblocks::content-blocks._preview-sql', ['sql' => ['countSql' => '', 'dataSql' => '']])
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<template id="field-row-template">
    @include('contentblocks::content-blocks._field-row', ['index' => '__INDEX__', 'field' => ['field' => 'title', 'label' => 'Title', 'class' => 'content-block__title', 'id' => 'content-block-new-block-title', 'sort_order' => 0], 'fieldOptions' => $fieldOptions])
</template>

<template id="filter-row-template">
    @include('contentblocks::content-blocks._filter-row', ['index' => '__INDEX__', 'filter' => ['field' => '', 'operator' => '', 'value' => ''], 'fieldOptions' => $fieldOptions])
</template>

@push('scripts')
<script>
(() => {
    const form = document.querySelector('.content-block-editor');
    if (!form) return;

    const fieldMeta = JSON.parse(form.dataset.fieldMeta || '{}');
    const operatorsByType = JSON.parse(form.dataset.operatorsByType || '{}');
    const operatorLabels = JSON.parse(form.dataset.operatorLabels || '{}');
    const blockKeyInput = form.querySelector('[name="key"]');

    function slugifyField(field) {
        return String(field || '').replace(/\./g, '-');
    }

    function defaultClass(field) {
        return 'content-block__' + slugifyField(field);
    }

    function defaultId(field) {
        const key = (blockKeyInput?.value || 'new-block').trim() || 'new-block';
        return 'content-block-' + key + '-' + slugifyField(field);
    }

    function nextIndex(container, selector) {
        return container.querySelectorAll(selector).length;
    }

    function bindFieldRow(row) {
        const fieldSelect = row.querySelector('.field-key-select');
        const labelInput = row.querySelector('.field-label-input');
        const classInput = row.querySelector('.field-class-input');
        const idInput = row.querySelector('.field-id-input');

        fieldSelect?.addEventListener('change', () => {
            const field = fieldSelect.value;
            const meta = fieldMeta[field] || {};
            if (labelInput && !labelInput.dataset.touched) {
                labelInput.value = meta.label || field;
            }
            if (classInput && !classInput.dataset.touched) {
                classInput.value = defaultClass(field);
            }
            if (idInput && !idInput.dataset.touched) {
                idInput.value = defaultId(field);
            }
        });

        [labelInput, classInput, idInput].forEach((input) => {
            input?.addEventListener('input', () => {
                input.dataset.touched = '1';
            });
        });

        row.querySelector('.remove-field-row')?.addEventListener('click', () => row.remove());
    }

    function bindFilterRow(row) {
        const fieldSelect = row.querySelector('.filter-field-select');
        const operatorSelect = row.querySelector('.filter-operator-select');

        function refreshOperators() {
            const field = fieldSelect.value;
            const type = fieldMeta[field]?.type || 'text';
            const operators = operatorsByType[type] || [];
            const current = operatorSelect.value;
            operatorSelect.innerHTML = '<option value="">Select operator</option>';
            operators.forEach((operator) => {
                const option = document.createElement('option');
                option.value = operator;
                option.textContent = operatorLabels[operator] || operator;
                if (operator === current) option.selected = true;
                operatorSelect.appendChild(option);
            });
        }

        fieldSelect?.addEventListener('change', refreshOperators);
        refreshOperators();
        row.querySelector('.remove-filter-row')?.addEventListener('click', () => row.remove());
    }

    document.getElementById('add-field-row')?.addEventListener('click', () => {
        const container = document.getElementById('field-rows');
        const index = nextIndex(container, '.field-row');
        const html = document.getElementById('field-row-template').innerHTML.replaceAll('__INDEX__', String(index));
        container.insertAdjacentHTML('beforeend', html);
        bindFieldRow(container.lastElementChild);
    });

    document.getElementById('add-filter-row')?.addEventListener('click', () => {
        const container = document.getElementById('filter-rows');
        const index = nextIndex(container, '.filter-row');
        const html = document.getElementById('filter-row-template').innerHTML.replaceAll('__INDEX__', String(index));
        container.insertAdjacentHTML('beforeend', html);
        bindFilterRow(container.lastElementChild);
    });

    document.querySelectorAll('.field-row').forEach(bindFieldRow);
    document.querySelectorAll('.filter-row').forEach(bindFilterRow);

    const previewRoute = form.dataset.previewRoute;
    const previewMeta = document.getElementById('preview-meta');
    const previewResults = document.getElementById('preview-results');
    const previewPagination = document.getElementById('preview-pagination');
    const previewSql = document.getElementById('preview-sql');
    const previewSqlSection = document.getElementById('preview-sql-section');
    const showSqlPreview = document.getElementById('show-sql-preview');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    let previewPage = 1;
    let previewInFlight = false;

    function syncSqlSectionVisibility() {
        if (! previewSqlSection || ! showSqlPreview) return;

        previewSqlSection.classList.toggle('d-none', ! showSqlPreview.checked);
    }

    showSqlPreview?.addEventListener('change', syncSqlSectionVisibility);
    syncSqlSectionVisibility();

    function renderPreviewPagination(meta) {
        if (! previewPagination) return;
        previewPagination.innerHTML = '';

        if (! meta?.paginationEnabled || (meta.lastPage ?? 1) <= 1) {
            return;
        }

        const nav = document.createElement('nav');
        nav.setAttribute('aria-label', 'Preview pagination');
        const list = document.createElement('ul');
        list.className = 'pagination pagination-sm mb-0';

        for (let page = 1; page <= meta.lastPage; page += 1) {
            const item = document.createElement('li');
            item.className = 'page-item' + (page === meta.page ? ' active' : '');
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'page-link';
            button.textContent = String(page);
            button.addEventListener('click', () => retrievePreview(page));
            item.appendChild(button);
            list.appendChild(item);
        }

        nav.appendChild(list);
        previewPagination.appendChild(nav);
    }

    async function retrievePreview(page = 1) {
        if (! previewRoute || ! previewMeta || ! previewResults) return;

        if (previewInFlight) return;

        previewInFlight = true;
        previewPage = page;
        previewMeta.textContent = 'Retrieving…';
        previewResults.innerHTML = '';
        if (previewPagination) previewPagination.innerHTML = '';
        if (previewSql) {
            previewSql.innerHTML = '<span class="text-muted">Retrieving…</span>';
        }

        const formData = new FormData(form);
        formData.append('preview_page', String(page));

        try {
            const response = await fetch(previewRoute, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });

            if (! response.ok) {
                const payload = await response.json().catch(() => ({}));
                const message = payload.message || 'Unable to retrieve preview.';
                previewMeta.textContent = message;
                if (previewSql) {
                    previewSql.innerHTML = '<span class="text-muted">Unable to generate SQL.</span>';
                }
                return;
            }

            const payload = await response.json();
            previewMeta.innerHTML = payload.metaHtml || formatPreviewMeta(payload.meta);
            previewResults.innerHTML = payload.html || '';
            renderPreviewPagination(payload.meta);

            if (previewSql) {
                previewSql.innerHTML = payload.sqlHtml || formatPreviewSql(payload.sql);
            }
        } catch (error) {
            previewMeta.textContent = 'Unable to retrieve preview.';
            if (previewSql) {
                previewSql.innerHTML = '<span class="text-muted">Unable to generate SQL.</span>';
            }
        } finally {
            previewInFlight = false;
        }
    }

    function formatPreviewSql(sql) {
        if (! sql?.countSql && ! sql?.dataSql) {
            return '<span class="text-muted">Click Retrieve to generate SQL for the current configuration.</span>';
        }

        const countSql = String(sql.countSql || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        const dataSql = String(sql.dataSql || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

        return '<pre class="mb-0 small bg-white border rounded p-2"><code>-- Count query\n'
            + countSql
            + '\n\n-- Data query\n'
            + dataSql
            + '</code></pre>';
    }

    function formatPreviewMeta(meta) {
        if (! meta || meta.totalMatching === 0) {
            return 'No published content matches the current configuration.';
        }

        let text = meta.totalMatching + ' item(s) match. Showing ' + meta.retrieved;

        if (meta.paginationEnabled) {
            text += ' on page ' + meta.page + ' of ' + meta.lastPage + ' (' + meta.perPage + ' per page).';
        } else if (meta.limitedTo) {
            text += ' (limited to ' + meta.limitedTo + ').';
        } else {
            text += '.';
        }

        text += ' Formatter: ' + meta.formatter + '.';

        return text;
    }

    document.getElementById('retrieve-preview')?.addEventListener('click', () => retrievePreview(1));
})();
</script>
@endpush
