<?php

namespace App\Modules\Media\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('media'));
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'caption' => ['nullable', 'string'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'copyright' => ['nullable', 'string', 'max:255'],
            'credit' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable'],
            'folder_id' => ['nullable', 'exists:media_folders,id'],
        ];
    }
}
