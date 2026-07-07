<?php

namespace App\Modules\ContentBlocks\Requests\Concerns;

use App\Modules\ContentBlocks\Enums\ContentBlockFormatter;
use App\Modules\ContentBlocks\Support\ContentBlockFieldRegistry;
use App\Modules\ContentBlocks\Support\ContentBlockFilterOperators;
use App\Support\ContentTypeRegistry;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

trait ValidatesContentBlockFields
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'pagination_enabled' => $this->boolean('pagination_enabled'),
        ]);
    }

    protected function contentBlockRules(bool $updating = false): array
    {
        $keyRule = $updating
            ? ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('content_blocks', 'key')->ignore($this->route('contentBlock'))]
            : ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:content_blocks,key'];

        return [
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['required', 'string', 'max:100'],
            'key' => $keyRule,
            'status' => ['required', Rule::enum(\App\Enums\ContentStatus::class)],
            'content_types' => ['required', 'array', 'min:1'],
            'content_types.*' => ['string', Rule::in(app(ContentTypeRegistry::class)->keys())],
            'fields' => ['required', 'array', 'min:1'],
            'fields.*.field' => ['required', 'string'],
            'fields.*.label' => ['nullable', 'string', 'max:255'],
            'fields.*.class' => ['nullable', 'string', 'max:255'],
            'fields.*.id' => ['nullable', 'string', 'max:255'],
            'fields.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'fields.*.link_to_content' => ['nullable', 'boolean'],
            'filters' => ['nullable', 'array'],
            'filters.*.field' => ['nullable', 'string'],
            'filters.*.operator' => ['nullable', 'string'],
            'filters.*.value' => ['nullable'],
            'sort_field' => ['required', 'string'],
            'sort_direction' => ['required', Rule::in(['asc', 'desc'])],
            'items_per_page' => ['required', 'integer', 'min:1', 'max:100'],
            'pagination_enabled' => ['nullable', 'boolean'],
            'formatter' => ['required', Rule::enum(ContentBlockFormatter::class)],
            'wrapper_class' => ['nullable', 'string', 'max:255'],
            'wrapper_id' => ['nullable', 'string', 'max:255'],
            'settings' => ['nullable', 'array'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $registry = app(ContentBlockFieldRegistry::class);
            $operators = app(ContentBlockFilterOperators::class);

            foreach ($this->input('fields', []) as $index => $field) {
                $fieldKey = (string) ($field['field'] ?? '');

                if ($fieldKey !== '' && ! $registry->has($fieldKey)) {
                    $validator->errors()->add("fields.{$index}.field", 'Invalid field selected.');
                }
            }

            $sortField = (string) $this->input('sort_field', '');

            if ($sortField !== '' && ! $registry->has($sortField)) {
                $validator->errors()->add('sort_field', 'Invalid sort field selected.');
            }

            foreach ($this->input('filters', []) as $index => $filter) {
                $fieldKey = (string) ($filter['field'] ?? '');
                $operator = (string) ($filter['operator'] ?? '');

                if ($fieldKey === '' && $operator === '') {
                    continue;
                }

                if (! $registry->has($fieldKey)) {
                    $validator->errors()->add("filters.{$index}.field", 'Invalid filter field selected.');

                    continue;
                }

                $allowed = $operators->forField($fieldKey, $registry);

                if ($operator !== '' && ! in_array($operator, $allowed, true)) {
                    $validator->errors()->add("filters.{$index}.operator", 'Invalid operator for the selected field.');
                }
            }
        });
    }
}
