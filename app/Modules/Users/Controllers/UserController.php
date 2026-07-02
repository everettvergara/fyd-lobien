<?php

namespace App\Modules\Users\Controllers;

use App\Enums\UserStatus;
use App\Framework\Admin\List\AdminBulkActionService;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Modules\Address\Models\City;
use App\Modules\Address\Models\Province;
use App\Modules\Authentication\Services\ProfileAvatarService;
use App\Modules\Users\Requests\StoreUserRequest;
use App\Modules\Users\Requests\UpdateUserRequest;
use App\Modules\Users\Services\UserAdminListService;
use App\Modules\Users\Services\UserManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        protected UserAdminListService $userList,
        protected UserManagementService $users,
        protected ProfileAvatarService $avatars,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        return view('users::users.index', [
            'list' => $this->userList->result($request),
        ]);
    }

    public function bulk(Request $request, AdminBulkActionService $bulkActions): RedirectResponse
    {
        $this->authorize('viewAny', User::class);

        $count = $bulkActions->execute($this->userList->definition(), $request);

        return back()->with('success', "{$count} user(s) updated successfully.");
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        $roles = Role::orderBy('display_name')->get();
        $statuses = UserStatus::cases();

        return view('users::users.create', compact('roles', 'statuses'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->users->create(
            [
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'status' => $request->enum('status', UserStatus::class),
            ],
            $request->filled('roles') ? $request->roles : null,
        );

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user): View
    {
        $this->authorize('view', $user);

        $user->load(['roles', 'avatar', 'province', 'city']);

        return view('users::users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        $user->load(['roles', 'avatar', 'province', 'city']);
        $roles = Role::orderBy('display_name')->get();
        $statuses = UserStatus::cases();
        $provinces = Province::query()->active()->orderBy('name')->get();
        $cities = $user->province_id
            ? City::query()->active()->where('province_id', $user->province_id)->orderBy('name')->get()
            : collect();

        return view('users::users.edit', compact('user', 'roles', 'statuses', 'provinces', 'cities'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->avatars->sync(
            $user,
            $request->input('avatar_media_id') ? (int) $request->input('avatar_media_id') : null,
            $request->boolean('remove_avatar'),
            $request->file('avatar'),
        );

        $this->users->update(
            $user,
            array_merge([
                'name' => $request->name,
                'email' => $request->email,
                'status' => $request->enum('status', UserStatus::class),
            ], $request->profileAttributes()),
            $request->roles ?? [],
            $request->filled('password') ? $request->password : null,
        );

        $this->avatars->registerUsage($user->fresh());

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $this->users->delete($user);

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function activate(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $this->users->activate($user);

        return back()->with('success', 'User activated successfully.');
    }

    public function deactivate(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $this->users->deactivate($user);

        return back()->with('success', 'User deactivated successfully.');
    }

    public function suspend(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $this->users->suspend($user);

        return back()->with('success', 'User suspended successfully.');
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $this->users->sendPasswordReset($user);

        return back()->with('success', 'Password reset link sent to user.');
    }
}
