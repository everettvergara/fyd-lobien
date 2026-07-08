<?php

namespace App\Modules\WebForms\Requests;

use App\Http\Requests\Public\Concerns\RequiresRecaptcha;
use App\Modules\WebForms\Models\Webform;
use App\Modules\WebForms\Services\WebformPublicService;
use App\Modules\WebForms\Services\WebformValidationService;
use Illuminate\Foundation\Http\FormRequest;

class SubmitWebformRequest extends FormRequest
{
    use RequiresRecaptcha;

    protected ?Webform $webform = null;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $webform = $this->resolveWebform();

        if ($webform === null) {
            return [];
        }

        return [
            'fields' => ['required', 'array'],
            ...app(WebformValidationService::class)->rulesForSchema($webform->schema ?? []),
            ...$this->recaptchaRules('webform_'.$webform->slug),
        ];
    }

    public function webform(): ?Webform
    {
        return $this->resolveWebform();
    }

    protected function resolveWebform(): ?Webform
    {
        if ($this->webform !== null) {
            return $this->webform;
        }

        $slug = (string) $this->route('slug');

        return $this->webform = app(WebformPublicService::class)->findActiveBySlug($slug);
    }
}
