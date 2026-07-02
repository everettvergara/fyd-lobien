<?php

namespace App\Modules\SiteReports\Services;

use App\Framework\Admin\List\AdminListFilter;
use App\Framework\Admin\List\AdminListState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

abstract class SiteReportAdminListService
{
    public function __construct(
        protected SiteVisitService $visits,
    ) {}

    protected function periodFilter(): AdminListFilter
    {
        return AdminListFilter::make(
            'period',
            'Period',
            'select',
            [
                '24h' => 'Last 24 hours',
                '7d' => 'Last 7 days',
            ],
            fn (Builder $query, string $value) => null,
            default: '7d',
        );
    }

    protected function resolveSince(Request $request, AdminListState $state): Carbon
    {
        return $this->visits->sinceFromPeriod($state->filters['period'] ?? '7d');
    }
}
