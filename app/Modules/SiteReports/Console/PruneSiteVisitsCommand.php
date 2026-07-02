<?php

namespace App\Modules\SiteReports\Console;

use App\Modules\SiteReports\Services\SiteVisitService;
use Illuminate\Console\Command;

class PruneSiteVisitsCommand extends Command
{
    protected $signature = 'site-reports:prune';

    protected $description = 'Delete site visit records older than one week';

    public function handle(SiteVisitService $visits): int
    {
        $cutoff = now()->subWeek();
        $deleted = $visits->pruneOlderThan($cutoff);

        $this->info("Deleted {$deleted} site visit record(s) older than {$cutoff->toDateTimeString()}.");

        return self::SUCCESS;
    }
}
