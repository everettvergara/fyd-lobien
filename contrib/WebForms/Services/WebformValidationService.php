<?php

namespace App\Modules\WebForms\Services;

class WebformValidationService
{
    /**
     * @param  array<string, mixed>  $schema
     * @return array<string, array<int, mixed>>
     */
    public function rulesForSchema(array $schema): array
    {
        $rules = [];

        foreach ($schema['fields'] ?? [] as $field) {
            $key = (string) ($field['key'] ?? '');

            if ($key === '') {
                continue;
            }

            $fieldRules = [($field['required'] ?? false) ? 'required' : 'nullable'];
            $type = (string) ($field['type'] ?? 'text');

            match ($type) {
                'email' => $fieldRules[] = 'email',
                'number' => $fieldRules[] = 'numeric',
                'date', 'datetime' => $fieldRules[] = 'date',
                'checkbox' => $fieldRules[] = 'boolean',
                'select', 'radio' => $this->appendInRule($fieldRules, $field),
                default => $fieldRules[] = 'string',
            };

            if (in_array($type, ['text', 'email', 'tel', 'textarea', 'hidden'], true)) {
                if (isset($field['validation']['min'])) {
                    $fieldRules[] = 'min:'.$field['validation']['min'];
                }

                if (isset($field['validation']['max'])) {
                    $fieldRules[] = 'max:'.$field['validation']['max'];
                }
            }

            if ($type === 'number') {
                if (isset($field['validation']['min'])) {
                    $fieldRules[] = 'min:'.$field['validation']['min'];
                }

                if (isset($field['validation']['max'])) {
                    $fieldRules[] = 'max:'.$field['validation']['max'];
                }
            }

            $rules['fields.'.$key] = $fieldRules;
        }

        return $rules;
    }

    /**
     * @param  array<int, mixed>  $fieldRules
     * @param  array<string, mixed>  $field
     */
    protected function appendInRule(array &$fieldRules, array $field): void
    {
        $values = collect($field['options'] ?? [])
            ->pluck('value')
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->values()
            ->all();

        if ($values !== []) {
            $fieldRules[] = 'in:'.implode(',', array_map(
                fn ($value) => is_numeric($value) ? $value : str_replace(',', '\,', (string) $value),
                $values,
            ));
        }
    }
}
