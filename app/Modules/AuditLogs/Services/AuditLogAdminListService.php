<?php

namespace App\Modules\AuditLogs\Services;

use App\Framework\Admin\List\AdminListColumn;
use App\Framework\Admin\List\AdminListDefinition;
use App\Framework\Admin\List\AdminListResult;
use App\Framework\Admin\List\AdminListService;
use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AuditLogAdminListService
{
    public function __construct(
        protected AdminListService $lists,
    ) {}

    public function result(Request $request): AdminListResult
    {
        $query = ActivityLog::query()->with('user');

        return $this->lists->build($query, $this->definition(), $request);
    }

    public function definition(): AdminListDefinition
    {
        return new AdminListDefinition(
            id: 'audit-logs',
            title: 'Audit Logs',
            modelClass: ActivityLog::class,
            columns: $this->columns(),
            searchQuery: fn (Builder $query, string $search) => $query->where(function (Builder $query) use ($search) {
                $query->where('module', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhereHas('user', function (Builder $query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            }),
            searchPlaceholder: 'Search module, action, or user...',
            defaultSort: 'created_at',
            defaultDirection: 'desc',
            defaultPerPage: 25,
            selectable: false,
        );
    }

    protected function columns(): array
    {
        return [
            AdminListColumn::make('no', 'No', class: 'text-muted small', headerClass: 'text-muted'),
            AdminListColumn::make('id', 'ID', sortField: 'id', class: 'text-muted small', headerClass: 'text-muted'),
            AdminListColumn::make('created_at', 'When', fn (ActivityLog $log) => $log->created_at->format('M j, Y g:i A'), sortField: 'created_at', class: 'text-muted small'),
            AdminListColumn::make('user', 'User', fn (ActivityLog $log) => e($log->user?->name ?? 'System'), class: 'small'),
            AdminListColumn::make('module', 'Module', fn (ActivityLog $log) => sprintf(
                '<span class="badge bg-secondary-subtle text-secondary">%s</span>',
                e($log->module),
            ), sortField: 'module', raw: true),
            AdminListColumn::make('action', 'Action', fn (ActivityLog $log) => sprintf(
                '<span class="badge bg-primary-subtle text-primary">%s</span>',
                e($log->action),
            ), sortField: 'action', raw: true),
            AdminListColumn::make('description', 'Description', fn (ActivityLog $log) => e($log->description()), class: 'small'),
        ];
    }
}
