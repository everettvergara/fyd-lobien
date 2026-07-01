<?php

namespace App\Modules\AuditLogs\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ActivityLog::class);

        $query = ActivityLog::with('user')->latest('created_at');

        if ($module = $request->get('module')) {
            $query->where('module', $module);
        }

        if ($action = $request->get('action')) {
            $query->where('action', $action);
        }

        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }

        $logs = $query->paginate(25)->withQueryString();

        $modules = ActivityLog::query()->distinct()->orderBy('module')->pluck('module');
        $actions = ActivityLog::query()->distinct()->orderBy('action')->pluck('action');

        return view('auditlogs::audit-logs.index', [
            'logs' => $logs,
            'modules' => $modules,
            'actions' => $actions,
            'filters' => $request->only(['module', 'action', 'user_id']),
        ]);
    }
}
