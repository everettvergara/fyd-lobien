<?php

namespace App\Modules\Users\Services;

use App\Enums\UserStatus;
use App\Framework\Admin\List\AdminBulkAction;
use App\Framework\Admin\List\AdminListAction;
use App\Framework\Admin\List\AdminListColumn;
use App\Framework\Admin\List\AdminListDefinition;
use App\Framework\Admin\List\AdminListResult;
use App\Framework\Admin\List\AdminListService;
use App\Models\User;
use App\Support\AdminIcon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class UserAdminListService
{
    public function __construct(
        protected AdminListService $lists,
        protected UserManagementService $users,
    ) {}

    public function result(Request $request): AdminListResult
    {
        $query = User::query()->with(['roles', 'avatar']);

        return $this->lists->build($query, $this->definition(), $request);
    }

    public function definition(): AdminListDefinition
    {
        return new AdminListDefinition(
            id: 'users',
            title: 'Users',
            modelClass: User::class,
            columns: $this->columns(),
            rowActions: $this->rowActions(),
            searchFields: ['name', 'email'],
            searchPlaceholder: 'Search name or email...',
            defaultSort: 'updated_at',
            defaultDirection: 'desc',
            defaultPerPage: 15,
            bulkActions: $this->bulkActions(),
        );
    }

    protected function columns(): array
    {
        return [
            AdminListColumn::make('no', 'No', class: 'text-muted small', headerClass: 'text-muted'),
            AdminListColumn::make('id', 'ID', sortField: 'id', class: 'text-muted small', headerClass: 'text-muted'),
            AdminListColumn::make('avatar', 'Photo', fn (User $user) => $this->avatarThumbnail($user), class: 'small', headerClass: 'text-muted', raw: true),
            AdminListColumn::make('name', 'Name', fn (User $user) => sprintf(
                '<a href="%s" class="text-decoration-none fw-medium">%s</a>',
                route('admin.users.show', $user),
                e($user->name),
            ), sortField: 'name', raw: true),
            AdminListColumn::make('email', 'Email', 'email', sortField: 'email', class: 'small'),
            AdminListColumn::make('roles', 'Roles', fn (User $user) => $user->roles
                ->map(fn ($role) => sprintf('<span class="badge bg-secondary-subtle text-secondary me-1">%s</span>', e($role->display_name)))
                ->join(''), raw: true),
            AdminListColumn::make('status', 'Status', fn (User $user) => sprintf(
                '<span class="badge bg-primary-subtle text-primary">%s</span>',
                e($user->status->label()),
            ), sortField: 'status', raw: true),
            AdminListColumn::make('last_login_at', 'Last Login', fn (User $user) => $user->last_login_at?->diffForHumans() ?? 'Never', sortField: 'last_login_at', class: 'text-muted small'),
        ];
    }

    protected function avatarThumbnail(User $user): string
    {
        if ($url = $user->avatarUrl()) {
            return sprintf(
                '<img src="%s" alt="%s" class="rounded-circle object-fit-cover border" width="36" height="36" style="object-fit:cover;">',
                e($url),
                e($user->name),
            );
        }

        return sprintf(
            '<span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light border text-muted" style="width:36px;height:36px;"><i class="%s"></i></span>',
            e(AdminIcon::solid('bi-person')),
        );
    }

    protected function rowActions(): array
    {
        return [
            AdminListAction::make('view', 'View', 'bi-eye', fn (User $user) => route('admin.users.show', $user), ability: 'view'),
            AdminListAction::make('edit', 'Edit', 'bi-pencil', fn (User $user) => route('admin.users.edit', $user), ability: 'update'),
            AdminListAction::make(
                'activate',
                'Activate',
                'bi-check-circle',
                fn (User $user) => route('admin.users.activate', $user),
                method: 'POST',
                ability: 'update',
                visible: fn (User $user) => $user->status !== UserStatus::Active,
            ),
            AdminListAction::make(
                'deactivate',
                'Deactivate',
                'bi-x-circle',
                fn (User $user) => route('admin.users.deactivate', $user),
                method: 'POST',
                ability: 'update',
                visible: fn (User $user) => $user->status !== UserStatus::Inactive,
            ),
            AdminListAction::make(
                'suspend',
                'Suspend',
                'bi-slash-circle',
                fn (User $user) => route('admin.users.suspend', $user),
                method: 'POST',
                ability: 'update',
                visible: fn (User $user) => $user->status !== UserStatus::Suspended,
            ),
            AdminListAction::make(
                'reset-password',
                'Reset Password',
                'bi-key',
                fn (User $user) => route('admin.users.reset-password', $user),
                method: 'POST',
                ability: 'update',
            ),
            AdminListAction::make(
                'delete',
                'Delete',
                'bi-trash',
                fn (User $user) => route('admin.users.destroy', $user),
                method: 'DELETE',
                ability: 'delete',
                confirm: 'Are you sure you want to delete this user?',
                danger: true,
            ),
        ];
    }

    protected function bulkActions(): array
    {
        $statusOptions = fn () => collect(UserStatus::cases())
            ->mapWithKeys(fn (UserStatus $status) => [$status->value => $status->label()])
            ->all();

        return [
            AdminBulkAction::make(
                'update_status',
                'Update status',
                'update',
                fn (Collection $users, Request $request) => $this->users->bulkUpdateStatus(
                    $users,
                    UserStatus::from((string) $request->input('bulk_status')),
                ),
                'Update status for selected users?',
                inputName: 'bulk_status',
                inputLabel: 'Status',
                inputOptions: $statusOptions,
            ),
            AdminBulkAction::make(
                'verify_email',
                'Verify email',
                'update',
                fn (Collection $users) => $this->users->bulkVerifyEmail($users),
                'Mark selected users as email verified?',
            ),
            AdminBulkAction::make(
                'unverify_email',
                'Unverify email',
                'update',
                fn (Collection $users) => $this->users->bulkUnverifyEmail($users),
                'Clear email verification for selected users?',
                danger: true,
            ),
        ];
    }
}
