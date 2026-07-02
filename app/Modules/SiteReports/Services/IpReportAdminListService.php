<?php

namespace App\Modules\SiteReports\Services;

use App\Framework\Admin\List\AdminListAction;
use App\Framework\Admin\List\AdminListColumn;
use App\Framework\Admin\List\AdminListDefinition;
use App\Framework\Admin\List\AdminListResult;
use App\Framework\Admin\List\AdminListService;
use App\Framework\Admin\List\AdminListState;
use App\Modules\SiteReports\Models\BlockedIp;
use App\Modules\SiteReports\Models\IpVisitStat;
use App\Modules\SiteReports\Models\SiteVisit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class IpReportAdminListService extends SiteReportAdminListService
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
            ->leftJoin('blocked_ips', 'site_visits.ip_address', '=', 'blocked_ips.ip_address')
            ->select('site_visits.ip_address')
            ->selectRaw('COUNT(*) as hit_count')
            ->selectRaw('MAX(site_visits.visited_at) as last_visited_at')
            ->selectRaw('MAX(blocked_ips.id) as blocked_ip_id')
            ->where('site_visits.visited_at', '>=', $since)
            ->groupBy('site_visits.ip_address');

        $query = IpVisitStat::query()->fromSub($subQuery, 'ip_stats');

        return $this->lists->build($query, $definition, $request);
    }

    public function definition(): AdminListDefinition
    {
        return new AdminListDefinition(
            id: 'site-report-ips',
            title: 'Hits by IP Address',
            modelClass: IpVisitStat::class,
            columns: $this->columns(),
            filters: [$this->periodFilter()],
            rowActions: $this->rowActions(),
            searchQuery: fn (Builder $query, string $search) => $query->where('ip_address', 'like', "%{$search}%"),
            searchPlaceholder: 'Search IP address...',
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
            AdminListColumn::make('ip_address', 'IP Address', 'ip_address', sortField: 'ip_address', class: 'font-monospace small'),
            AdminListColumn::make('hit_count', 'Hits', 'hit_count', sortField: 'hit_count', class: 'fw-medium'),
            AdminListColumn::make('status', 'Status', fn (IpVisitStat $stat) => $stat->blocked_ip_id
                ? '<span class="badge bg-danger-subtle text-danger">Blocked</span>'
                : '<span class="badge bg-success-subtle text-success">Allowed</span>', class: 'small', raw: true),
            AdminListColumn::make('last_visited_at', 'Last Visit', fn (IpVisitStat $stat) => $stat->last_visited_at?->diffForHumans() ?? '—', sortField: 'last_visited_at', class: 'text-muted small'),
        ];
    }

    protected function rowActions(): array
    {
        return [
            AdminListAction::make(
                'block',
                'Block IP',
                'bi-shield-x',
                fn (IpVisitStat $stat) => route('admin.site-reports.blocked-ips.store', ['ip' => $stat->ip_address]),
                method: 'POST',
                ability: 'create',
                abilityTarget: BlockedIp::class,
                visible: fn (IpVisitStat $stat) => ! $stat->blocked_ip_id,
                confirm: 'Block this IP address from accessing the public site?',
                danger: true,
            ),
            AdminListAction::make(
                'unblock',
                'Unblock IP',
                'bi-shield-check',
                fn (IpVisitStat $stat) => route('admin.site-reports.blocked-ips.destroy', ['blockedIp' => $stat->blocked_ip_id]),
                method: 'DELETE',
                ability: 'delete',
                abilityTarget: fn (IpVisitStat $stat) => BlockedIp::query()->find($stat->blocked_ip_id) ?? new BlockedIp(['ip_address' => $stat->ip_address]),
                visible: fn (IpVisitStat $stat) => (bool) $stat->blocked_ip_id,
                confirm: 'Unblock this IP address?',
            ),
        ];
    }
}
