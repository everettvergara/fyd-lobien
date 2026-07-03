<?php

namespace App\Modules\Careers\Requests;

use App\Http\Requests\Public\Concerns\RequiresRecaptcha;
use App\Modules\Careers\Models\CareerJob;
use App\Modules\Careers\Services\CareerApplicationStorageService;
use App\Modules\Careers\Services\CareerPublicService;
use Illuminate\Foundation\Http\FormRequest;

class SubmitCareerApplicationRequest extends FormRequest
{
    use RequiresRecaptcha;

    protected ?CareerJob $job = null;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $job = $this->resolveJob();

        if ($job === null) {
            return [];
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'contact_number' => ['required', 'string', 'max:50'],
            'remarks' => ['nullable', 'string', 'max:5000'],
            'resume' => ['required', 'file', 'mimes:pdf', 'max:'.CareerApplicationStorageService::MAX_KB],
            ...$this->recaptchaRules('career_apply_'.$job->slug),
        ];
    }

    public function job(): ?CareerJob
    {
        return $this->resolveJob();
    }

    protected function resolveJob(): ?CareerJob
    {
        if ($this->job !== null) {
            return $this->job;
        }

        $slug = (string) $this->route('slug');

        return $this->job = app(CareerPublicService::class)->findOpenJobBySlug($slug);
    }
}
