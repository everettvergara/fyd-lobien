<?php

namespace App\Http\Requests\Public;

use App\Http\Requests\Public\Concerns\RequiresRecaptcha;
use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    use RequiresRecaptcha;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'max:255'],
            ...$this->recaptchaRules('search'),
        ];
    }
}
