<?php

namespace App\Modules\PropertyListings\Requests;

use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Requests\Concerns\ValidatesListingFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateListingRequest extends FormRequest
{
    use ValidatesListingFields;

    public function authorize(): bool
    {
        $listing = $this->route('listing');

        return $listing instanceof Listing
            ? ($this->user()?->can('update', $listing) ?? false)
            : false;
    }

    protected function prepareForValidation(): void
    {
        $this->prepareListingFieldsForValidation();
    }

    public function rules(): array
    {
        /** @var Listing $listing */
        $listing = $this->route('listing');

        return array_merge($this->listingFieldRules(), [
            'code' => ['required', 'string', 'max:255', Rule::unique('listings', 'code')->ignore($listing->id)],
        ]);
    }
}
