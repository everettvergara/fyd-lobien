<?php

namespace App\Modules\PropertyListings\Requests;

use App\Modules\PropertyListings\Models\Listing;
use Illuminate\Foundation\Http\FormRequest;

class ListingAssetBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('batchAssets', Listing::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'files' => ['nullable', 'array', 'max:200'],
            'files.*' => ['file', 'max:51200'],
            'archive' => ['nullable', 'file', 'mimes:zip', 'max:102400'],
            'typed_files' => ['nullable', 'array'],
            'typed_files.*' => ['nullable', 'array'],
            'typed_files.*.*' => ['file', 'max:51200'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->isListingScoped()) {
                $typedFiles = [];

                foreach ($this->file('typed_files') ?? [] as $files) {
                    foreach ((array) $files as $file) {
                        if ($file instanceof \Illuminate\Http\UploadedFile) {
                            $typedFiles[] = $file;
                        }
                    }
                }

                if ($typedFiles === []) {
                    $validator->errors()->add('typed_files', 'Choose at least one file to upload.');
                }

                return;
            }

            $hasFiles = is_array($this->file('files')) && count($this->file('files')) > 0;
            $hasArchive = $this->hasFile('archive');

            if (! $hasFiles && ! $hasArchive) {
                $validator->errors()->add('files', 'Upload one or more asset files or a ZIP archive.');
            }

            if ($hasFiles && $hasArchive) {
                $validator->errors()->add('archive', 'Upload either individual files or a ZIP archive, not both.');
            }
        });
    }

    protected function isListingScoped(): bool
    {
        return $this->route('listing') instanceof Listing;
    }
}
