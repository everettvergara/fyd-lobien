<?php

namespace App\Modules\Users\Controllers;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Modules\Users\Requests\StoreUserRequest;
use App\Modules\Users\Requests\UpdateUserRequest;
use App\Notifications\AccountActivatedNotification;
use App\Notifications\AccountDeactivatedNotification;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class UserController extends Controller
{
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
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => $request->enum('status', UserStatus::class),
            'email_verified_at' => $request->enum('status', UserStatus::class) === UserStatus::Active ? now() : null,
        ]);

        if ($request->filled('roles')) {
            $user->syncRoles($request->roles);
        }

        ActivityLogger::log('users', 'created', $user, ['name' => $user->name]);

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
        $user->fill([
            'name' => $request->name,
            'email' => $request->email,
            'status' => $request->enum('status', UserStatus::class),
        ]);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        $user->syncRoles($request->roles ?? []);

        ActivityLogger::log('users', 'updated', $user, ['name' => $user->name]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        ActivityLogger::log('users', 'deleted', $user, ['name' => $user->name]);

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function activate(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $user->update(['status' => UserStatus::Active]);
        $user->notify(new AccountActivatedNotification);

        ActivityLogger::log('users', 'activated', $user);

        return back()->with('success', 'User activated successfully.');
    }

    public function deactivate(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $user->update(['status' => UserStatus::Inactive]);
        $user->notify(new AccountDeactivatedNotification);

        ActivityLogger::log('users', 'deactivated', $user);

        return back()->with('success', 'User deactivated successfully.');
    }

    public function suspend(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $user->update(['status' => UserStatus::Suspended]);

        ActivityLogger::log('users', 'suspended', $user);

        return back()->with('success', 'User suspended successfully.');
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        Password::sendResetLink(['email' => $user->email]);

        ActivityLogger::log('users', 'updated', $user, ['action' => 'password_reset_sent']);

        return back()->with('success', 'Password reset link sent to user.');
    }
}
