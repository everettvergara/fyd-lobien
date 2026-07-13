<?php

namespace App\Modules\PropertyListings\Requests;

use App\Modules\PropertyListings\Models\Listing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreListingRemarkRequest extends FormRequest
{
    public function authorize(): bool
    {
        $listing = $this->route('listing');

        return $listing instanceof Listing
            ? ($this->user()?->can('update', $listing) ?? false)
            : false;
    }

    public function rules(): array
    {
        /** @var Listing $listing */
        $listing = $this->route('listing');

        return [
            'listing_unit_id' => [
                'nullable',
                'integer',
                Rule::exists('listing_units', 'id')->where('listing_id', $listing->id),
            ],
            'comment' => ['required', 'string', 'max:5000'],
        ];
    }
}
