<?php

namespace App\Modules\DemoNotes\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDemoNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('demo_note')) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
        ];
    }
}
