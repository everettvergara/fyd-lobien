<?php

namespace App\Modules\Posts\Requests;

use App\Enums\ContentStatus;
use App\Support\SeoFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->can('create', \App\Modules\Posts\Models\Post::class); }

    public function rules(): array
    {
        return array_merge([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:posts,slug'],
            'summary' => ['nullable', 'string'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(ContentStatus::class)],
            'published_at' => ['nullable', 'date'],
        ], SeoFields::rules());
    }
}
