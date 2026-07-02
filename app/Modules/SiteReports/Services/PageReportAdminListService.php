<?php

namespace App\Modules\SiteReports\Services;

use App\Framework\Admin\List\AdminListColumn;
use App\Framework\Admin\List\AdminListDefinition;
use App\Framework\Admin\List\AdminListResult;
use App\Framework\Admin\List\AdminListService;
use App\Framework\Admin\List\AdminListState;
use App\Modules\SiteReports\Models\PageVisitStat;
use App\Modules\SiteReports\Models\SiteVisit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PageReportAdminListService extends SiteReportAdminListService
{
    public function __construct(
        SiteVisitService $visits,
        protected AdminListService $lists,
    ) {
        parent::__construct($visits);
    }

    public function result(Request $request): AdminListResult
    {
        $definition = $this->definition();
        $state = AdminListState::fromRequest($request, $definition);
        $since = $this->resolveSince($request, $state);

        $subQuery = SiteVisit::query()
            ->select('path')
            ->selectRaw('MAX(route_name) as route_name')
            ->selectRaw('COUNT(*) as hit_count')
            ->selectRaw('MAX(visited_at) as last_visited_at')
            ->where('visited_at', '>=', $since)
            ->groupBy('path');

        $query = PageVisitStat::query()->fromSub($subQuery, 'page_stats');

        return $this->lists->build($query, $definition, $request);
    }

    public function definition(): AdminListDefinition
    {
        return new AdminListDefinition(
            id: 'site-report-pages',
            title: 'Most Visited Pages',
            modelClass: PageVisitStat::class,
            columns: $this->columns(),
            filters: [$this->periodFilter()],
            searchQuery: fn (Builder $query, string $search) => $query->where(function (Builder $query) use ($search) {
                $query->where('path', 'like', "%{$search}%")
                    ->orWhere('route_name', 'like', "%{$search}%");
            }),
            searchPlaceholder: 'Search path or route...',
            defaultSort: 'hit_count',
            defaultDirection: 'desc',
            defaultPerPage: 25,
            selectable: false,
        );
    }

    protected function columns(): array
    {
        return [
            AdminListColumn::make('no', 'No', class: 'text-muted small', headerClass: 'text-muted'),
            AdminListColumn::make('path', 'Path', 'path', sortField: 'path'),
            AdminListColumn::make('route_name', 'Route', fn (PageVisitStat $stat) => e($stat->route_name ?? '—'), sortField: 'route_name', class: 'small text-muted'),
            AdminListColumn::make('hit_count', 'Hits', 'hit_count', sortField: 'hit_count', class: 'fw-medium'),
            AdminListColumn::make('last_visited_at', 'Last Visit', fn (PageVisitStat $stat) => $stat->last_visited_at?->diffForHumans() ?? '—', sortField: 'last_visited_at', class: 'text-muted small'),
        ];
    }
}
