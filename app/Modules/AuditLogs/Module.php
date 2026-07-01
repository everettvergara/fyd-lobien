<?php

namespace App\Modules\AuditLogs;

use App\Models\ActivityLog;
use App\Modules\AuditLogs\Policies\AuditLogPolicy;

class Module extends \App\Framework\Module
{
    public function name(): string
    {
        return 'AuditLogs';
    }

    public function policies(): array
    {
        return [
            ActivityLog::class => AuditLogPolicy::class,
        ];
    }

    public function menuItems(): array
    {
        return [
            $this->menuItem('Audit Logs', 'admin.audit-logs.index', 'activity_log.view', 'bi-clock-history', 'Administration', sort: 95),
        ];
    }
}
