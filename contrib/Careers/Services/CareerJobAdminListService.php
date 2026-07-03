<?php

namespace App\Modules\Careers\Services;

use App\Framework\Admin\List\AdminListAction;
use App\Framework\Admin\List\AdminListColumn;
use App\Framework\Admin\List\AdminListDefinition;
use App\Framework\Admin\List\AdminListResult;
use App\Framework\Admin\List\AdminListService;
use App\Modules\Careers\Models\CareerJob;
use Illuminate\Http\Request;

class CareerJobAdminListService
{
    public function __construct(
        protected AdminListService $lists,
    ) {}

    public function result(Request $request): AdminListResult
    {
        $query = CareerJob::query()->withCount('applications');

        return $this->lists->build($query, $this->definition(), $request);
    }

    public function definition(): AdminListDefinition
    {
        return new AdminListDefinition(
            id: 'career-jobs',
            title: 'Job Listings',
            modelClass: CareerJob::class,
            columns: [
                AdminListColumn::make('no', 'No', class: 'text-muted small', headerClass: 'text-muted'),
                AdminListColumn::make('id', 'ID', sortField: 'id', class: 'text-muted small', headerClass: 'text-muted'),
                AdminListColumn::make('title', 'Title', fn (CareerJob $job) => sprintf(
                    '<a href="%s" class="text-decoration-none">%s</a>',
                    route('admin.career-jobs.edit', $job),
                    e($job->title),
                ), sortField: 'title', raw: true),
                AdminListColumn::make('department', 'Department', fn (CareerJob $job) => e($job->department ?? '—'), sortField: 'department', class: 'small text-muted'),
                AdminListColumn::make('location', 'Location', fn (CareerJob $job) => e($job->location ?? '—'), sortField: 'location', class: 'small text-muted'),
                AdminListColumn::make('employment_type', 'Type', fn (CareerJob $job) => e($job->employmentTypeLabel()), sortField: 'employment_type', class: 'small'),
                AdminListColumn::make('status', 'Status', fn (CareerJob $job) => view('components.admin.status-badge', [
                    'status' => $job->isPublished() ? 'Published' : 'Draft',
                    'variant' => $job->isPublished() ? 'success' : 'secondary',
                ])->render(), sortField: 'status', raw: true),
                AdminListColumn::make('closing_date', 'Closing', fn (CareerJob $job) => $job->closing_date?->format('Y-m-d') ?? '—', sortField: 'closing_date', class: 'small text-muted'),
                AdminListColumn::make('applications_count', 'Applications', fn (CareerJob $job) => (string) $job->applications_count, sortField: 'applications_count', class: 'small'),
            ],
            rowActions: [
                AdminListAction::make('edit', 'Edit', 'bi-pencil', fn (CareerJob $job) => route('admin.career-jobs.edit', $job), ability: 'update'),
                AdminListAction::make('applications', 'Applications', 'bi-inbox', fn (CareerJob $job) => route('admin.career-applications.index', ['job' => $job->id]), ability: 'view'),
                AdminListAction::make('delete', 'Delete', 'bi-trash', fn (CareerJob $job) => route('admin.career-jobs.destroy', $job), method: 'DELETE', ability: 'delete', confirm: 'Delete this job listing and all applications?', danger: true),
            ],
            searchFields: ['title', 'slug', 'department', 'location'],
            searchPlaceholder: 'Search title, slug, department, or location...',
            defaultSort: 'sort_order',
            defaultDirection: 'asc',
            defaultPerPage: 15,
        );
    }
}
