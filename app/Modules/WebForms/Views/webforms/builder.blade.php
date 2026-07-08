@extends('admin.layouts.app')

@section('title', 'Form Builder — '.$webform->name)

@section('content')
    <x-admin.breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Webforms', 'url' => route('admin.webforms.index')],
        ['label' => $webform->name, 'url' => route('admin.webforms.edit', $webform)],
        ['label' => 'Builder'],
    ]" />

    <x-admin.page-header title="Form Builder — {{ $webform->name }}">
        <x-slot:actions>
            <a href="{{ url('/'.$webform->slug) }}" class="btn btn-outline-secondary" target="_blank" rel="noopener">
                <i class="bi bi-box-arrow-up-right me-1"></i> Preview
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    @if ($errors->any())
        <div class="alert alert-danger">
            <div class="fw-semibold mb-1">Could not save form fields:</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div id="builderClientAlert" class="alert alert-danger d-none" role="alert"></div>

    <form method="POST" action="{{ route('admin.webforms.builder.update', $webform) }}" id="webformBuilderForm" class="card border-0 shadow-sm">
        @csrf
        @method('PUT')

        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Fields</h2>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addFieldBtn">
                    <i class="bi bi-plus-lg me-1"></i> Add field
                </button>
            </div>

            <div id="fieldsContainer" class="vstack gap-3"></div>
            <p id="emptyFieldsMessage" class="text-muted small mb-0">No fields yet. Add at least one field before saving.</p>

            <hr class="my-4">

            <h2 class="h5 mb-3">Form settings</h2>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="submit_label">Submit button label</label>
                    <input type="text" class="form-control" id="submit_label" data-setting="submit_label">
                </div>
                <div class="col-md-8">
                    <label class="form-label" for="success_message">Success message</label>
                    <input type="text" class="form-control" id="success_message" data-setting="success_message">
                </div>
                <div class="col-12">
                    <label class="form-label" for="redirect_url">Redirect URL (optional)</label>
                    <input type="text" class="form-control" id="redirect_url" data-setting="redirect_url" placeholder="https://example.com/thank-you">
                </div>
            </div>

            <input type="hidden" name="schema" id="schemaInput" value="">
        </div>

        <div class="card-footer bg-white border-top d-flex gap-2">
            <button type="submit" class="btn btn-primary">Save fields</button>
            <a href="{{ route('admin.webforms.edit', $webform) }}" class="btn btn-outline-secondary">Back to settings</a>
        </div>
    </form>

    <template id="fieldTemplate">
        <div class="card border" data-field-card>
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start gap-2" data-field-summary>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <strong data-field-title>Untitled field</strong>
                        <span class="badge text-bg-secondary" data-field-type-badge>text</span>
                        <code class="small text-muted" data-field-key-display></code>
                        <span class="badge text-bg-danger" data-required-badge hidden>Required</span>
                    </div>
                    <div class="btn-group btn-group-sm flex-shrink-0">
                        <button type="button" class="btn btn-outline-primary" data-edit-field title="Edit field">
                            <i class="bi bi-pencil me-1"></i> Edit
                        </button>
                        <button type="button" class="btn btn-outline-secondary" data-move-up title="Move up"><i class="bi bi-arrow-up"></i></button>
                        <button type="button" class="btn btn-outline-secondary" data-move-down title="Move down"><i class="bi bi-arrow-down"></i></button>
                        <button type="button" class="btn btn-outline-danger" data-remove-field title="Remove"><i class="bi bi-trash"></i></button>
                    </div>
                </div>

                <div class="mt-3 pt-3 border-top" data-field-editor hidden>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Type</label>
                            <select class="form-select" data-field="type"></select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Key</label>
                            <input type="text" class="form-control" data-field="key" pattern="[A-Za-z0-9_\-]+">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Label</label>
                            <input type="text" class="form-control" data-field="label">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Placeholder</label>
                            <input type="text" class="form-control" data-field="placeholder">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Help text</label>
                            <input type="text" class="form-control" data-field="help">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Min</label>
                            <input type="number" class="form-control" data-field="validation.min">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Max</label>
                            <input type="number" class="form-control" data-field="validation.max">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" data-field="required">
                                <label class="form-check-label">Required</label>
                            </div>
                        </div>
                        <div class="col-12" data-options-wrap hidden>
                            <label class="form-label">Options (value | label per line)</label>
                            <textarea class="form-control font-monospace small" rows="4" data-field="options" placeholder="general | General inquiry"></textarea>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-sm btn-primary" data-done-field>
                            <i class="bi bi-check-lg me-1"></i> Done
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
@endsection

@push('scripts')
<script>
(() => {
    const fieldTypes = @json($fieldTypes);
    let schema = {!! $schemaJson !!};
    const container = document.getElementById('fieldsContainer');
    const template = document.getElementById('fieldTemplate');
    const emptyMessage = document.getElementById('emptyFieldsMessage');
    const form = document.getElementById('webformBuilderForm');
    const schemaInput = document.getElementById('schemaInput');
    const clientAlert = document.getElementById('builderClientAlert');
    let expandedCard = null;

    const settingsFields = {
        submit_label: document.getElementById('submit_label'),
        success_message: document.getElementById('success_message'),
        redirect_url: document.getElementById('redirect_url'),
    };

    function defaultField() {
        return {
            key: '',
            type: 'text',
            label: '',
            placeholder: '',
            help: '',
            required: false,
            options: [],
            validation: { min: '', max: '' },
        };
    }

    function slugify(value) {
        return value
            .trim()
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '')
            .replace(/_+/g, '_');
    }

    function parseOptions(text) {
        return text.split('\n')
            .map((line) => line.trim())
            .filter(Boolean)
            .map((line) => {
                const [value, ...rest] = line.split('|');
                const label = rest.join('|').trim();
                return { value: value.trim(), label: label || value.trim() };
            });
    }

    function formatOptions(options) {
        return (options || []).map((option) => `${option.value} | ${option.label}`).join('\n');
    }

    function optionTypes(type) {
        return ['select', 'radio'].includes(type);
    }

    function showClientAlert(message) {
        clientAlert.textContent = message;
        clientAlert.classList.remove('d-none');
        clientAlert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function hideClientAlert() {
        clientAlert.classList.add('d-none');
        clientAlert.textContent = '';
    }

    function readFieldCard(card) {
        const field = defaultField();
        field.type = card.querySelector('[data-field="type"]').value;
        field.key = card.querySelector('[data-field="key"]').value.trim();
        field.label = card.querySelector('[data-field="label"]').value.trim();
        field.placeholder = card.querySelector('[data-field="placeholder"]').value.trim();
        field.help = card.querySelector('[data-field="help"]').value.trim();
        field.required = card.querySelector('[data-field="required"]').checked;
        const min = card.querySelector('[data-field="validation.min"]').value;
        const max = card.querySelector('[data-field="validation.max"]').value;
        field.validation.min = min === '' ? null : Number(min);
        field.validation.max = max === '' ? null : Number(max);
        if (optionTypes(field.type)) {
            field.options = parseOptions(card.querySelector('[data-field="options"]').value);
        }
        return field;
    }

    function updateSummary(card) {
        const field = readFieldCard(card);
        card.querySelector('[data-field-title]').textContent = field.label || field.key || 'Untitled field';
        card.querySelector('[data-field-type-badge]').textContent = field.type || 'text';
        card.querySelector('[data-field-key-display]').textContent = field.key ? field.key : 'no key';
        card.querySelector('[data-required-badge]').hidden = !field.required;
    }

    function fillFieldCard(card, field) {
        const typeSelect = card.querySelector('[data-field="type"]');
        typeSelect.innerHTML = fieldTypes.map((type) => `<option value="${type}">${type}</option>`).join('');
        typeSelect.value = field.type || 'text';
        card.querySelector('[data-field="key"]').value = field.key || '';
        card.querySelector('[data-field="label"]').value = field.label || '';
        card.querySelector('[data-field="placeholder"]').value = field.placeholder || '';
        card.querySelector('[data-field="help"]').value = field.help || '';
        card.querySelector('[data-field="required"]').checked = Boolean(field.required);
        card.querySelector('[data-field="validation.min"]').value = field.validation?.min ?? '';
        card.querySelector('[data-field="validation.max"]').value = field.validation?.max ?? '';
        const optionsWrap = card.querySelector('[data-options-wrap]');
        const optionsField = card.querySelector('[data-field="options"]');
        if (optionTypes(typeSelect.value)) {
            optionsWrap.hidden = false;
            optionsField.value = formatOptions(field.options);
        } else {
            optionsWrap.hidden = true;
            optionsField.value = '';
        }
        updateSummary(card);
    }

    function collapseCard(card) {
        card.querySelector('[data-field-editor]').hidden = true;
        card.querySelector('[data-field-summary]').classList.remove('d-none');
        if (expandedCard === card) {
            expandedCard = null;
        }
    }

    function expandCard(card) {
        if (expandedCard && expandedCard !== card) {
            updateSummary(expandedCard);
            collapseCard(expandedCard);
        }
        card.querySelector('[data-field-editor]').hidden = false;
        card.querySelector('[data-field-summary]').classList.remove('d-none');
        expandedCard = card;
    }

    function renderFields() {
        container.innerHTML = '';
        expandedCard = null;
        (schema.fields || []).forEach((field) => {
            const node = template.content.cloneNode(true);
            const card = node.querySelector('[data-field-card]');
            fillFieldCard(card, field);
            bindFieldCard(card);
            collapseCard(card);
            container.appendChild(node);
        });
        emptyMessage.hidden = (schema.fields || []).length > 0;
    }

    function bindFieldCard(card) {
        card.querySelector('[data-edit-field]').addEventListener('click', () => {
            expandCard(card);
        });

        card.querySelector('[data-done-field]').addEventListener('click', () => {
            updateSummary(card);
            collapseCard(card);
            syncSchemaFromDom();
        });

        card.querySelector('[data-remove-field]').addEventListener('click', () => {
            if (expandedCard === card) {
                expandedCard = null;
            }
            card.remove();
            syncSchemaFromDom();
            emptyMessage.hidden = container.children.length > 0;
        });

        card.querySelector('[data-move-up]').addEventListener('click', () => {
            const prev = card.previousElementSibling;
            if (prev) {
                card.parentNode.insertBefore(card, prev);
                syncSchemaFromDom();
            }
        });

        card.querySelector('[data-move-down]').addEventListener('click', () => {
            const next = card.nextElementSibling;
            if (next) {
                card.parentNode.insertBefore(next, card);
                syncSchemaFromDom();
            }
        });

        card.querySelector('[data-field="type"]').addEventListener('change', () => {
            const optionsWrap = card.querySelector('[data-options-wrap]');
            optionsWrap.hidden = !optionTypes(card.querySelector('[data-field="type"]').value);
            updateSummary(card);
        });

        const keyInput = card.querySelector('[data-field="key"]');
        const labelInput = card.querySelector('[data-field="label"]');

        labelInput.addEventListener('input', () => {
            if (keyInput.value.trim() === '') {
                keyInput.value = slugify(labelInput.value);
            }
            updateSummary(card);
        });

        card.querySelectorAll('input, textarea, select').forEach((input) => {
            if (input === labelInput) {
                return;
            }
            input.addEventListener('input', () => updateSummary(card));
            input.addEventListener('change', () => updateSummary(card));
        });
    }

    function syncSchemaFromDom() {
        schema.fields = Array.from(container.querySelectorAll('[data-field-card]')).map(readFieldCard);
        schema.settings = {
            submit_label: settingsFields.submit_label.value.trim() || 'Submit',
            success_message: settingsFields.success_message.value.trim() || 'Thank you for your submission.',
            redirect_url: settingsFields.redirect_url.value.trim() || null,
        };
    }

    document.getElementById('addFieldBtn').addEventListener('click', () => {
        hideClientAlert();
        const node = template.content.cloneNode(true);
        const card = node.querySelector('[data-field-card]');
        fillFieldCard(card, defaultField());
        bindFieldCard(card);
        container.appendChild(node);
        expandCard(card);
        emptyMessage.hidden = true;
    });

    form.addEventListener('submit', (event) => {
        hideClientAlert();
        if (expandedCard) {
            updateSummary(expandedCard);
            collapseCard(expandedCard);
        }
        syncSchemaFromDom();

        const keys = schema.fields.map((field) => field.key).filter(Boolean);
        const uniqueKeys = new Set(keys);

        if (schema.fields.length === 0) {
            event.preventDefault();
            showClientAlert('Add at least one field before saving.');
            return;
        }

        if (keys.length !== schema.fields.length || uniqueKeys.size !== keys.length) {
            event.preventDefault();
            showClientAlert('Each field must have a unique, non-empty key.');
            return;
        }

        for (const field of schema.fields) {
            if (optionTypes(field.type) && (!field.options || field.options.length === 0)) {
                event.preventDefault();
                showClientAlert(`The "${field.label || field.key}" field needs at least one option.`);
                return;
            }
        }

        schemaInput.value = JSON.stringify(schema);
    });

    settingsFields.submit_label.value = schema.settings?.submit_label || 'Submit';
    settingsFields.success_message.value = schema.settings?.success_message || 'Thank you for your submission.';
    settingsFields.redirect_url.value = schema.settings?.redirect_url || '';

    renderFields();
})();
</script>
@endpush
