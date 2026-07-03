<?php

namespace App\Modules\WebForms\Services;

use App\Framework\Admin\List\AdminBulkAction;
use App\Framework\Admin\List\AdminListAction;
use App\Framework\Admin\List\AdminListColumn;
use App\Framework\Admin\List\AdminListDefinition;
use App\Framework\Admin\List\AdminListFilter;
use App\Framework\Admin\List\AdminListResult;
use App\Framework\Admin\List\AdminListService;
use App\Modules\WebForms\Models\Webform;
use App\Modules\WebForms\Models\WebformSubmission;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WebformSubmissionAdminListService
{
    public function __construct(
        protected AdminListService $lists,
    ) {}

    public function result(Request $request): AdminListResult
    {
        $query = WebformSubmission::query()
            ->with('webform')
            ->leftJoin('webforms', 'webforms.id', '=', 'webform_submissions.webform_id')
            ->select('webform_submissions.*');

        return $this->lists->build($query, $this->definition(), $request);
    }

    public function definition(): AdminListDefinition
    {
        return new AdminListDefinition(
            id: 'webform-submissions',
            title: 'Submissions',
            modelClass: WebformSubmission::class,
            columns: [
                AdminListColumn::make('no', 'No', class: 'text-muted small', headerClass: 'text-muted'),
                AdminListColumn::make('id', 'ID', sortField: 'webform_submissions.id', class: 'text-muted small', headerClass: 'text-muted'),
                AdminListColumn::make('form_name', 'Form', fn (WebformSubmission $submission) => $submission->webform
                    ? e($submission->webform->name)
                    : '<span class="text-muted">—</span>', sortField: 'webforms.name', raw: true),
                AdminListColumn::make('created_at', 'Submitted', fn (WebformSubmission $submission) => $submission->created_at->format('Y-m-d H:i'), sortField: 'webform_submissions.created_at', class: 'small'),
                AdminListColumn::make('preview', 'Preview', fn (WebformSubmission $submission) => e($submission->previewText()), class: 'small text-muted'),
                AdminListColumn::make('ip_address', 'IP', fn (WebformSubmission $submission) => e($submission->ip_address ?? '—'), sortField: 'webform_submissions.ip_address', class: 'small text-muted'),
            ],
            filters: [
                AdminListFilter::make(
                    'webform',
                    'Form',
                    'select',
                    fn () => Webform::query()->orderBy('name')->pluck('name', 'id')->all(),
                    fn (Builder $query, mixed $value) => $query->where('webform_submissions.webform_id', $value),
                ),
            ],
            rowActions: [
                AdminListAction::make('view', 'View', 'bi-eye', fn (WebformSubmission $submission) => route('admin.webform-submissions.show', $submission), ability: 'view'),
                AdminListAction::make('delete', 'Delete', 'bi-trash', fn (WebformSubmission $submission) => route('admin.webform-submissions.destroy', $submission), method: 'DELETE', ability: 'delete', confirm: 'Delete this submission?', danger: true),
            ],
            bulkActions: [
                AdminBulkAction::make('delete', 'Delete selected', 'delete', fn (Collection $submissions, Request $request) => $this->bulkDelete($submissions), 'Delete selected submissions?', danger: true),
            ],
            searchQuery: fn (Builder $query, string $search) => $this->applySearch($query, $search),
            searchPlaceholder: 'Search ID, form, or field values...',
            defaultSort: 'created_at',
            defaultDirection: 'desc',
            defaultPerPage: 25,
        );
    }

    protected function applySearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $query) use ($search) {
            if (is_numeric($search)) {
                $query->orWhere('webform_submissions.id', $search);
            }

            $query->orWhereHas('webform', function (Builder $query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });

            $driver = DB::connection()->getDriverName();

            if ($driver === 'mysql') {
                $query->orWhereRaw('JSON_SEARCH(webform_submissions.data, "all", ?) IS NOT NULL', ["%{$search}%"]);
            } else {
                $query->orWhereRaw('CAST(webform_submissions.data AS TEXT) LIKE ?', ["%{$search}%"]);
            }
        });
    }

    protected function bulkDelete(Collection $submissions): int
    {
        $submissions->each(function (WebformSubmission $submission) {
            ActivityLogger::log('webforms', 'submission_deleted', $submission);
            $submission->delete();
        });

        return $submissions->count();
    }
}
