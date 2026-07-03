<?php

namespace App\Modules\DemoNotes\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDemoTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Modules\DemoNotes\Models\DemoTag::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:demo_tags,name'],
        ];
    }
}
