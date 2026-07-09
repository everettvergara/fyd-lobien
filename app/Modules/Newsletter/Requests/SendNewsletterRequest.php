<?php

namespace App\Modules\Newsletter\Requests;

use App\Support\HtmlSanitizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendNewsletterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('send', \App\Modules\Newsletter\Models\NewsletterSend::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'newsletter_list_id' => ['required', Rule::exists('newsletter_lists', 'id')],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('body')) {
            $this->merge(['body' => HtmlSanitizer::clean($this->input('body'))]);
        }
    }
}
