<?php

namespace App\Rules;

use App\Modules\Content\Models\Content;
use App\Support\SlugValidation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PublishedContentPageUrl implements ValidationRule
{
    /**
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! is_string($value)) {
            $fail('The maintenance page URL must be a valid internal page path.');

            return;
        }

        $slug = ltrim($value, '/');

        if ($slug === '' || str_contains($slug, '/') || ! preg_match(SlugValidation::PATTERN, $slug)) {
            $fail('The maintenance page URL must be a valid internal page path.');

            return;
        }

        if (in_array($slug, ['blog', 'search', 'admin', 'api'], true)) {
            $fail('The maintenance page URL cannot use a reserved path.');

            return;
        }

        $exists = Content::query()
            ->published()
            ->where('slug', $slug)
            ->where('content_type', 'page')
            ->exists();

        if (! $exists) {
            $fail('The maintenance page URL must point to a published page.');
        }
    }
}
