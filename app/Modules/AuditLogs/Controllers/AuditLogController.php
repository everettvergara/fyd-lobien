<?php

namespace App\Modules\AuditLogs\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Modules\AuditLogs\Services\AuditLogAdminListService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function __construct(
        protected AuditLogAdminListService $auditLogList,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ActivityLog::class);

        return view('auditlogs::audit-logs.index', [
            'list' => $this->auditLogList->result($request),
        ]);
    }
}
