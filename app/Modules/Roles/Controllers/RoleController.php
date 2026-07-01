<?php

namespace App\Modules\Roles\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Modules\Roles\Requests\StoreRoleRequest;
use App\Modules\Roles\Requests\UpdateRoleRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::withCount(['users', 'permissions'])
            ->orderBy('display_name')
            ->paginate(15);

        return view('roles::roles.index', compact('roles'));
    }

    public function create(): View
    {
        $this->authorize('create', Role::class);

        $permissions = Permission::orderBy('module')->orderBy('action')->get()
            ->groupBy('module');

        return view('roles::roles.create', compact('permissions'));
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = Role::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'is_system' => false,
        ]);

        $role->syncPermissions($request->permissions ?? []);

        ActivityLogger::log('roles', 'created', $role, ['name' => $role->display_name]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function show(Role $role): View
    {
        $this->authorize('view', $role);

        $role->load(['permissions', 'users']);

        return view('roles::roles.show', compact('role'));
    }

    public function edit(Role $role): View
    {
        $this->authorize('update', $role);

        $role->load('permissions');
        $permissions = Permission::orderBy('module')->orderBy('action')->get()
            ->groupBy('module');

        return view('roles::roles.edit', compact('role', 'permissions'));
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $role->update([
            'display_name' => $request->display_name,
            'description' => $request->description,
        ]);

        if (! $role->is_system) {
            $role->syncPermissions($request->permissions ?? []);
        }

        ActivityLogger::log('roles', 'updated', $role, ['name' => $role->display_name]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        ActivityLogger::log('roles', 'deleted', $role, ['name' => $role->display_name]);

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}
