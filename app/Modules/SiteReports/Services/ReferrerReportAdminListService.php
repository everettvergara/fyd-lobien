<?php

namespace App\Modules\SiteReports\Services;

use App\Framework\Admin\List\AdminListColumn;
use App\Framework\Admin\List\AdminListDefinition;
use App\Framework\Admin\List\AdminListResult;
use App\Framework\Admin\List\AdminListService;
use App\Framework\Admin\List\AdminListState;
use App\Modules\SiteReports\Models\ReferrerVisitStat;
use App\Modules\SiteReports\Models\SiteVisit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ReferrerReportAdminListService extends SiteReportAdminListService
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
            ->select('referer_host')
            ->selectRaw('COUNT(*) as hit_count')
            ->selectRaw('MAX(visited_at) as last_visited_at')
            ->where('visited_at', '>=', $since)
            ->whereNotNull('referer_host')
            ->groupBy('referer_host');

        $query = ReferrerVisitStat::query()->fromSub($subQuery, 'referrer_stats');

        return $this->lists->build($query, $definition, $request);
    }

    public function summary(Request $request): array
    {
        $state = AdminListState::fromRequest($request, $this->definition());
        $since = $this->resolveSince($request, $state);

        $totalHits = SiteVisit::query()
            ->where('visited_at', '>=', $since)
            ->count();

        $referredHits = SiteVisit::query()
            ->where('visited_at', '>=', $since)
            ->whereNotNull('referer_host')
            ->count();

        return [
            'total_hits' => $totalHits,
            'referred_hits' => $referredHits,
            'direct_hits' => max(0, $totalHits - $referredHits),
        ];
    }

    public function definition(): AdminListDefinition
    {
        return new AdminListDefinition(
            id: 'site-report-referrers',
            title: 'Referring Sites',
            modelClass: ReferrerVisitStat::class,
            columns: $this->columns(),
            filters: [$this->periodFilter()],
            searchQuery: fn (Builder $query, string $search) => $query->where('referer_host', 'like', "%{$search}%"),
            searchPlaceholder: 'Search referring host...',
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
            AdminListColumn::make('referer_host', 'Referring Site', 'referer_host', sortField: 'referer_host'),
            AdminListColumn::make('hit_count', 'Hits', 'hit_count', sortField: 'hit_count', class: 'fw-medium'),
            AdminListColumn::make('last_visited_at', 'Last Visit', fn (ReferrerVisitStat $stat) => $stat->last_visited_at?->diffForHumans() ?? '—', sortField: 'last_visited_at', class: 'text-muted small'),
        ];
    }
}
