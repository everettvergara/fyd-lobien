<?php

namespace App\Modules\Careers\Services;

use App\Modules\Careers\Models\CareerJob;
use Illuminate\Support\Collection;

class CareerPublicService
{
    /**
     * @return Collection<int, CareerJob>
     */
    public function listOpenJobs(): Collection
    {
        return CareerJob::query()
            ->open()
            ->with('picture')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();
    }

    public function findOpenJobBySlug(string $slug): ?CareerJob
    {
        return CareerJob::query()
            ->open()
            ->with('picture')
            ->where('slug', $slug)
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    public function toListItemDto(CareerJob $job): array
    {
        return [
            'id' => $job->id,
            'title' => $job->title,
            'slug' => $job->slug,
            'summary' => $job->summary,
            'department' => $job->department,
            'location' => $job->location,
            'employment_type' => $job->employment_type,
            'employment_type_label' => $job->employmentTypeLabel(),
            'salary_range' => $job->salary_range,
            'closing_date' => $job->closing_date?->format('Y-m-d'),
            'picture' => $this->pictureDto($job),
            'url' => url('/careers/'.$job->slug),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDetailDto(CareerJob $job): array
    {
        return [
            ...$this->toListItemDto($job),
            'description' => $job->description,
            'requirements' => $job->requirements,
        ];
    }

    /**
     * @return array{url: string, alt: string}|null
     */
    protected function pictureDto(CareerJob $job): ?array
    {
        if ($job->picture === null) {
            return null;
        }

        return [
            'url' => $job->picture->url(),
            'alt' => $job->picture->alt_text ?? $job->title,
        ];
    }
}
