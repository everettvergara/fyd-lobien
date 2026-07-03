<?php

namespace App\Modules\WebForms\Services;

use App\Modules\WebForms\Models\Webform;
use App\Modules\WebForms\Models\WebformSubmission;
use Illuminate\Http\Request;

class WebformSubmissionService
{
    /**
     * @param  array<string, mixed>  $fields
     */
    public function store(Webform $webform, array $fields, Request $request): WebformSubmission
    {
        $normalized = $this->normalizeFields($webform, $fields);

        return WebformSubmission::create([
            'webform_id' => $webform->id,
            'data' => $normalized,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return array<string, mixed>
     */
    protected function normalizeFields(Webform $webform, array $fields): array
    {
        $normalized = [];

        foreach ($webform->fieldDefinitions() as $field) {
            $key = (string) ($field['key'] ?? '');

            if ($key === '') {
                continue;
            }

            $value = $fields[$key] ?? null;

            if (($field['type'] ?? '') === 'checkbox') {
                $normalized[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            } else {
                $normalized[$key] = is_scalar($value) || $value === null ? $value : null;
            }
        }

        return $normalized;
    }
}
