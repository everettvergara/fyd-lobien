<?php

namespace App\Modules\DemoNotes\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDemoNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Modules\DemoNotes\Models\DemoNote::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
        ];
    }
}
