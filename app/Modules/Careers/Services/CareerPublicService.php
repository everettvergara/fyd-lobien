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
    public function toPublicDto(CareerJob $job): array
    {
        return [
            'id' => $job->id,
            'title' => $job->title,
            'slug' => $job->slug,
            'summary' => $job->summary,
            'description' => $job->description,
            'requirements' => $job->requirements,
            'department' => $job->department,
            'location' => $job->location,
            'salary_range' => $job->salary_range,
            'employment_type' => $job->employment_type,
            'employment_type_label' => $job->employmentTypeLabel(),
            'closing_date' => $job->closing_date?->format('Y-m-d'),
            'published_at' => $job->published_at?->toIso8601String(),
            'sort_order' => $job->sort_order,
            'picture' => $this->pictureDto($job),
            'url' => url('/careers/'.$job->slug),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toListItemDto(CareerJob $job): array
    {
        return $this->toPublicDto($job);
    }

    /**
     * @return array<string, mixed>
     */
    public function toDetailDto(CareerJob $job): array
    {
        return $this->toPublicDto($job);
    }

    /**
     * @param  Collection<int, mixed>  $items
     * @return array{items: array<int, mixed>, pagination: array{current_page: int, last_page: int, per_page: int, total: int}}
     */
    public function paginateCollection(Collection $items, int $page, int $perPage): array
    {
        $perPage = max(1, $perPage);
        $total = $items->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $lastPage);

        return [
            'items' => $items->forPage($page, $perPage)->values()->all(),
            'pagination' => [
                'current_page' => $page,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'total' => $total,
            ],
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
