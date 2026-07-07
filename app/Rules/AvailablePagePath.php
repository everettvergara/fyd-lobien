<?php

namespace App\Rules;

use App\Modules\PageManager\Models\Page;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AvailablePagePath implements ValidationRule
{
    public function __construct(
        protected ?int $exceptPageId = null,
    ) {}

    /**
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! is_string($value)) {
            $fail('The public path must be a valid path.');

            return;
        }

        $path = Page::normalizePath($value);
        $occupant = Page::liveOccupantAtPath($path, $this->exceptPageId);

        if ($occupant === null) {
            return;
        }

        if ($occupant->is_system) {
            $fail("The public path `{$path}` is reserved by a system page.");

            return;
        }

        $fail("The public path `{$path}` is already used by another page.");
    }
}
