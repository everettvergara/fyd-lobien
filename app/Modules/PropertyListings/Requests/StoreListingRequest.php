<?php

namespace App\Modules\PropertyListings\Requests;

use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Requests\Concerns\ValidatesListingFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreListingRequest extends FormRequest
{
    use ValidatesListingFields;

    public function authorize(): bool
    {
        return $this->user()?->can('create', Listing::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->prepareListingFieldsForValidation();
    }

    public function rules(): array
    {
        return array_merge($this->listingFieldRules(), [
            'code' => ['required', 'string', 'max:255', Rule::unique('listings', 'code')],
        ]);
    }
}
