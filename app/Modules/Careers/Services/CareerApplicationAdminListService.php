<?php

namespace App\Modules\Careers\Services;

use App\Framework\Admin\List\AdminBulkAction;
use App\Framework\Admin\List\AdminListAction;
use App\Framework\Admin\List\AdminListColumn;
use App\Framework\Admin\List\AdminListDefinition;
use App\Framework\Admin\List\AdminListFilter;
use App\Framework\Admin\List\AdminListResult;
use App\Framework\Admin\List\AdminListService;
use App\Modules\Careers\Models\CareerApplication;
use App\Modules\Careers\Models\CareerJob;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class CareerApplicationAdminListService
{
    public function __construct(
        protected AdminListService $lists,
    ) {}

    public function result(Request $request): AdminListResult
    {
        $query = CareerApplication::query()
            ->with('job')
            ->leftJoin('career_jobs', 'career_jobs.id', '=', 'career_applications.career_job_id')
            ->select('career_applications.*');

        return $this->lists->build($query, $this->definition(), $request);
    }

    public function definition(): AdminListDefinition
    {
        return new AdminListDefinition(
            id: 'career-applications',
            title: 'Applications',
            modelClass: CareerApplication::class,
            columns: [
                AdminListColumn::make('no', 'No', class: 'text-muted small', headerClass: 'text-muted'),
                AdminListColumn::make('id', 'ID', sortField: 'career_applications.id', class: 'text-muted small', headerClass: 'text-muted'),
                AdminListColumn::make('job_title', 'Job', fn (CareerApplication $application) => $application->job
                    ? e($application->job->title)
                    : '<span class="text-muted">—</span>', sortField: 'career_jobs.title', raw: true),
                AdminListColumn::make('name', 'Name', fn (CareerApplication $application) => e($application->name), sortField: 'career_applications.name'),
                AdminListColumn::make('email', 'Email', fn (CareerApplication $application) => e($application->email), sortField: 'career_applications.email', class: 'small'),
                AdminListColumn::make('contact_number', 'Contact', fn (CareerApplication $application) => e($application->contact_number), sortField: 'career_applications.contact_number', class: 'small text-muted'),
                AdminListColumn::make('created_at', 'Submitted', fn (CareerApplication $application) => $application->created_at->format('Y-m-d H:i'), sortField: 'career_applications.created_at', class: 'small'),
                AdminListColumn::make('ip_address', 'IP', fn (CareerApplication $application) => e($application->ip_address ?? '—'), sortField: 'career_applications.ip_address', class: 'small text-muted'),
            ],
            filters: [
                AdminListFilter::make(
                    'job',
                    'Job Listing',
                    'select',
                    fn () => CareerJob::query()->orderBy('title')->pluck('title', 'id')->all(),
                    fn (Builder $query, mixed $value) => $query->where('career_applications.career_job_id', $value),
                ),
            ],
            rowActions: [
                AdminListAction::make('view', 'View', 'bi-eye', fn (CareerApplication $application) => route('admin.career-applications.show', $application), ability: 'view'),
                AdminListAction::make('delete', 'Delete', 'bi-trash', fn (CareerApplication $application) => route('admin.career-applications.destroy', $application), method: 'DELETE', ability: 'delete', confirm: 'Delete this application?', danger: true),
            ],
            bulkActions: [
                AdminBulkAction::make('delete', 'Delete selected', 'delete', fn (Collection $applications, Request $request) => $this->bulkDelete($applications), 'Delete selected applications?', danger: true),
            ],
            searchQuery: fn (Builder $query, string $search) => $this->applySearch($query, $search),
            searchPlaceholder: 'Search name, email, contact, or job...',
            defaultSort: 'created_at',
            defaultDirection: 'desc',
            defaultPerPage: 25,
        );
    }

    protected function applySearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $query) use ($search) {
            if (is_numeric($search)) {
                $query->orWhere('career_applications.id', $search);
            }

            $query->orWhere('career_applications.name', 'like', "%{$search}%")
                ->orWhere('career_applications.email', 'like', "%{$search}%")
                ->orWhere('career_applications.contact_number', 'like', "%{$search}%")
                ->orWhereHas('job', function (Builder $query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
        });
    }

    protected function bulkDelete(Collection $applications): int
    {
        $applications->each(function (CareerApplication $application) {
            if (Storage::disk('local')->exists($application->resume_path)) {
                Storage::disk('local')->delete($application->resume_path);
            }

            ActivityLogger::log('careers.applications', 'deleted', $application);
            $application->delete();
        });

        return $applications->count();
    }
}
