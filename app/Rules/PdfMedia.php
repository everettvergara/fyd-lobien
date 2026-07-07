<?php

namespace App\Rules;

use App\Models\Media;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PdfMedia implements ValidationRule
{
    /**
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $media = Media::find($value);

        if ($media === null || ! $media->isPdf()) {
            $fail('The attachment must be a PDF file.');
        }
    }
}
