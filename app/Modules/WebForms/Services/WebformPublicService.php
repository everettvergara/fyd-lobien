<?php

namespace App\Modules\WebForms\Services;

use App\Modules\WebForms\Models\Webform;

class WebformPublicService
{
    public function findActiveBySlug(string $slug): ?Webform
    {
        return Webform::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    public function toPublicDto(Webform $webform): array
    {
        $settings = $webform->settings();

        return [
            'slug' => $webform->slug,
            'name' => $webform->name,
            'description' => $webform->description,
            'fields' => collect($webform->fieldDefinitions())
                ->map(fn (array $field) => [
                    'key' => $field['key'],
                    'type' => $field['type'],
                    'label' => $field['label'] ?? $field['key'],
                    'placeholder' => $field['placeholder'] ?? '',
                    'help' => $field['help'] ?? '',
                    'required' => (bool) ($field['required'] ?? false),
                    'options' => $field['options'] ?? [],
                ])
                ->values()
                ->all(),
            'settings' => [
                'submit_label' => $settings['submit_label'] ?? 'Submit',
                'success_message' => $settings['success_message'] ?? 'Thank you for your submission.',
                'redirect_url' => $settings['redirect_url'] ?? null,
            ],
        ];
    }
}
