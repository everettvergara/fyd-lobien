<?php

namespace App\Modules\Newsletter\Requests;

use App\Http\Requests\Public\Concerns\RequiresRecaptcha;
use App\Modules\Newsletter\Models\NewsletterList;
use App\Modules\Newsletter\Services\NewsletterPublicService;
use Illuminate\Foundation\Http\FormRequest;

class SubscribeNewsletterRequest extends FormRequest
{
    use RequiresRecaptcha;

    protected ?NewsletterList $newsletterList = null;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $list = $this->resolveList();

        if ($list === null) {
            return [];
        }

        $rules = [
            ...$this->recaptchaRules('newsletter_'.$list->slug.'_subscribe'),
        ];

        if (! auth()->check()) {
            $rules['email'] = ['required', 'email', 'max:255'];
        }

        foreach ($list->fieldSettings() as $field => $config) {
            if (! ($config['enabled'] ?? false)) {
                continue;
            }

            $rules[$field] = [
                ($config['required'] ?? false) ? 'required' : 'nullable',
                'string',
                'max:255',
            ];
        }

        return $rules;
    }

    public function list(): ?NewsletterList
    {
        return $this->resolveList();
    }

    protected function resolveList(): ?NewsletterList
    {
        if ($this->newsletterList !== null) {
            return $this->newsletterList;
        }

        $slug = (string) $this->route('slug');

        return $this->newsletterList = app(NewsletterPublicService::class)->findActiveBySlug($slug);
    }
}
