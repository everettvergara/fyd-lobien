<?php

namespace App\Modules\Sessions\Services;

use App\Framework\Admin\List\AdminListAction;
use App\Framework\Admin\List\AdminListColumn;
use App\Framework\Admin\List\AdminListDefinition;
use App\Framework\Admin\List\AdminListResult;
use App\Framework\Admin\List\AdminListService;
use App\Models\DatabaseSession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SessionAdminListService
{
    public function __construct(
        protected AdminListService $lists,
    ) {}

    public function result(Request $request): AdminListResult
    {
        $query = DatabaseSession::query()->with('user');

        return $this->lists->build($query, $this->definition(), $request);
    }

    public function definition(): AdminListDefinition
    {
        return new AdminListDefinition(
            id: 'sessions',
            title: 'Sessions',
            modelClass: DatabaseSession::class,
            columns: $this->columns(),
            rowActions: $this->rowActions(),
            searchQuery: fn (Builder $query, string $search) => $query->where(function (Builder $query) use ($search) {
                $query->whereHas('user', function (Builder $query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }),
            searchPlaceholder: 'Search user name or email...',
            defaultSort: 'last_activity',
            defaultDirection: 'desc',
            defaultPerPage: 25,
        );
    }

    protected function columns(): array
    {
        return [
            AdminListColumn::make('no', 'No', class: 'text-muted small', headerClass: 'text-muted'),
            AdminListColumn::make('id', 'ID', sortField: 'id', class: 'text-muted small text-truncate', headerClass: 'text-muted'),
            AdminListColumn::make('user', 'User', fn (DatabaseSession $session) => $session->user
                ? sprintf('%s<br><span class="text-muted small">%s</span>', e($session->user->name), e($session->user->email))
                : '<span class="text-muted">Guest</span>', raw: true),
            AdminListColumn::make('ip_address', 'IP Address', 'ip_address', sortField: 'ip_address', class: 'small'),
            AdminListColumn::make('user_agent', 'User Agent', fn (DatabaseSession $session) => e(\Illuminate\Support\Str::limit($session->user_agent ?? '—', 60)), class: 'small text-truncate'),
            AdminListColumn::make('last_activity', 'Last Activity', fn (DatabaseSession $session) => $session->lastActiveAt()->diffForHumans(), sortField: 'last_activity', class: 'text-muted small'),
        ];
    }

    protected function rowActions(): array
    {
        return [
            AdminListAction::make(
                'revoke',
                'Revoke',
                'bi-x-circle',
                fn (DatabaseSession $session) => route('admin.sessions.destroy', $session),
                method: 'DELETE',
                ability: 'delete',
                confirm: 'Revoke this session?',
                danger: true,
            ),
        ];
    }
}
