<?php

namespace App\Modules\PropertyListings\Requests;

use App\Modules\PropertyListings\Models\Listing;
use Illuminate\Foundation\Http\FormRequest;

class UpdateListingPublishedRequest extends FormRequest
{
    public function authorize(): bool
    {
        $listing = $this->route('listing');

        return $listing instanceof Listing
            && ($this->user()?->can('update', $listing) ?? false);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'published_to_public' => filter_var(
                $this->input('published_to_public', false),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE,
            ) ?? false,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'published_to_public' => ['required', 'boolean'],
        ];
    }
}
