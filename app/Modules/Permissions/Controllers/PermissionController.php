<?php

namespace App\Modules\Permissions\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Permission::class);

        $permissions = Permission::orderBy('module')->orderBy('action')->get()
            ->groupBy('module');

        return view('permissions::permissions.index', compact('permissions'));
    }
}
