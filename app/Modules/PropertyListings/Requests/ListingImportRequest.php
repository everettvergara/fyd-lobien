<?php

namespace App\Modules\PropertyListings\Requests;

use App\Modules\PropertyListings\Models\Listing;
use Illuminate\Foundation\Http\FormRequest;

class ListingImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('import', Listing::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ];
    }
}
