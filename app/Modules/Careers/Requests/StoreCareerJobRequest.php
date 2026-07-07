<?php

namespace App\Modules\Careers\Requests;

use App\Modules\Careers\Models\CareerJob;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCareerJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', CareerJob::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('career_jobs', 'slug')],
            'picture_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'department' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'salary_range' => ['nullable', 'string', 'max:255'],
            'employment_type' => ['required', 'string', Rule::in(CareerJob::EMPLOYMENT_TYPES)],
            'summary' => ['nullable', 'string'],
            'description' => ['required', 'string'],
            'requirements' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in([CareerJob::STATUS_DRAFT, CareerJob::STATUS_PUBLISHED])],
            'published_at' => ['nullable', 'date'],
            'closing_date' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('slug') && $this->filled('title')) {
            $this->merge(['slug' => Str::slug($this->input('title'))]);
        }

        $this->merge([
            'picture_media_id' => $this->input('picture_media_id') ?: null,
            'sort_order' => $this->input('sort_order', 0),
        ]);
    }
}
