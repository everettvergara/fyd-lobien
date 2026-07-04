<div class="field-row border rounded p-3 mb-2">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small">Field</label>
            <select name="fields[{{ $index }}][field]" class="form-select form-select-sm field-key-select" required>
                @foreach ($fieldOptions as $value => $label)
                    <option value="{{ $value }}" @selected(($field['field'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small">Label</label>
            <input type="text" name="fields[{{ $index }}][label]" class="form-control form-control-sm field-label-input" value="{{ $field['label'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small">CSS Class</label>
            <input type="text" name="fields[{{ $index }}][class]" class="form-control form-control-sm field-class-input" value="{{ $field['class'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small">Element ID</label>
            <input type="text" name="fields[{{ $index }}][id]" class="form-control form-control-sm field-id-input" value="{{ $field['id'] ?? '' }}">
        </div>
        <div class="col-md-1">
            <input type="hidden" name="fields[{{ $index }}][sort_order]" value="{{ $field['sort_order'] ?? $index }}">
            <button type="button" class="btn btn-outline-danger btn-sm remove-field-row">&times;</button>
        </div>
    </div>
</div>
