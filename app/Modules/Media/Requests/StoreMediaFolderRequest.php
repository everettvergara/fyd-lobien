<?php

namespace App\Modules\Media\Requests;

use App\Models\Media;
use Illuminate\Foundation\Http\FormRequest;

class StoreMediaFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manageFolders', Media::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:media_folders,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
