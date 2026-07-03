<?php

namespace App\Modules\Careers\Requests;

use App\Modules\Careers\Models\CareerJob;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCareerJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        $job = $this->route('career_job');

        return $job instanceof CareerJob
            ? ($this->user()?->can('update', $job) ?? false)
            : false;
    }

    public function rules(): array
    {
        /** @var CareerJob $job */
        $job = $this->route('career_job');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('career_jobs', 'slug')->ignore($job->id)],
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
        $this->merge([
            'picture_media_id' => $this->input('picture_media_id') ?: null,
            'sort_order' => $this->input('sort_order', 0),
        ]);
    }
}
