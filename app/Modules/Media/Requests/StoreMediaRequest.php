<?php

namespace App\Modules\Media\Requests;

use App\Models\Media;
use Illuminate\Foundation\Http\FormRequest;

class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Media::class);
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:10240'],
            'folder_id' => ['nullable', 'exists:media_folders,id'],
            'alt_text' => ['nullable', 'string', 'max:255'],
        ];
    }
}
