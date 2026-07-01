<?php

namespace App\Modules\Users\Controllers;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Modules\Users\Requests\StoreUserRequest;
use App\Modules\Users\Requests\UpdateUserRequest;
use App\Modules\Users\Services\UserManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        protected UserManagementService $users,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $users = User::with('roles')
            ->latest()
            ->paginate(15);

        return view('users::users.index', compact('users'));
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

        $user->load('roles');

        return view('users::users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        $user->load('roles');
        $roles = Role::orderBy('display_name')->get();
        $statuses = UserStatus::cases();

        return view('users::users.edit', compact('user', 'roles', 'statuses'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->users->update(
            $user,
            [
                'name' => $request->name,
                'email' => $request->email,
                'status' => $request->enum('status', UserStatus::class),
            ],
            $request->roles ?? [],
            $request->filled('password') ? $request->password : null,
        );

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
