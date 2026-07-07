<?php

namespace App\Modules\WebForms\Requests;

use App\Modules\WebForms\Models\Webform;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateWebformSchemaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $webform = $this->route('webform');

        return $webform instanceof Webform
            && ($this->user()?->can('update', $webform) ?? false);
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('schema') && is_string($this->input('schema'))) {
            $decoded = json_decode($this->input('schema'), true);

            if (is_array($decoded)) {
                $this->merge(['schema' => $decoded]);
            }
        }

        $schema = $this->input('schema');

        if (! is_array($schema)) {
            return;
        }

        $fields = $schema['fields'] ?? [];

        foreach ($fields as $index => $field) {
            if (! is_array($field)) {
                continue;
            }

            $type = $field['type'] ?? 'text';

            if (! in_array($type, ['select', 'radio'], true)) {
                $fields[$index]['options'] = [];
            } elseif (! isset($field['options']) || ! is_array($field['options'])) {
                $fields[$index]['options'] = [];
            }
        }

        $schema['fields'] = $fields;
        $this->merge(['schema' => $schema]);
    }

    public function rules(): array
    {
        return [
            'schema' => ['required', 'array'],
            'schema.fields' => ['present', 'array'],
            'schema.fields.*.key' => ['required', 'string', 'max:100', 'alpha_dash'],
            'schema.fields.*.type' => ['required', 'string', Rule::in([
                'text', 'email', 'tel', 'number', 'textarea', 'select', 'radio', 'checkbox', 'date', 'datetime', 'hidden',
            ])],
            'schema.fields.*.label' => ['required', 'string', 'max:255'],
            'schema.fields.*.placeholder' => ['nullable', 'string', 'max:255'],
            'schema.fields.*.help' => ['nullable', 'string', 'max:500'],
            'schema.fields.*.required' => ['sometimes', 'boolean'],
            'schema.fields.*.options' => ['nullable', 'array'],
            'schema.fields.*.options.*.value' => ['required_with:schema.fields.*.options', 'string', 'max:255'],
            'schema.fields.*.options.*.label' => ['required_with:schema.fields.*.options', 'string', 'max:255'],
            'schema.fields.*.validation' => ['nullable', 'array'],
            'schema.fields.*.validation.min' => ['nullable', 'numeric'],
            'schema.fields.*.validation.max' => ['nullable', 'numeric'],
            'schema.settings' => ['required', 'array'],
            'schema.settings.submit_label' => ['required', 'string', 'max:100'],
            'schema.settings.success_message' => ['required', 'string', 'max:500'],
            'schema.settings.redirect_url' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'schema.fields.*.key.alpha_dash' => 'Each field key may only contain letters, numbers, dashes, and underscores.',
            'schema.fields.*.key.required' => 'Each field must have a key.',
            'schema.fields.*.label.required' => 'Each field must have a label.',
            'schema.fields.*.options.*.value.required_with' => 'Select and radio fields must include option values.',
            'schema.fields.*.options.*.label.required_with' => 'Select and radio fields must include option labels.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $fields = $this->input('schema.fields', []);

            if (! is_array($fields)) {
                return;
            }

            $keys = [];

            foreach ($fields as $index => $field) {
                if (! is_array($field)) {
                    continue;
                }

                $key = $field['key'] ?? '';

                if ($key !== '') {
                    if (isset($keys[$key])) {
                        $validator->errors()->add(
                            "schema.fields.{$index}.key",
                            "The field key \"{$key}\" is duplicated.",
                        );
                    }

                    $keys[$key] = true;
                }

                $type = $field['type'] ?? '';

                if (in_array($type, ['select', 'radio'], true)) {
                    $options = $field['options'] ?? [];

                    if (! is_array($options) || count($options) === 0) {
                        $validator->errors()->add(
                            "schema.fields.{$index}.options",
                            'Select and radio fields must have at least one option.',
                        );
                    }
                }
            }
        });
    }
}
