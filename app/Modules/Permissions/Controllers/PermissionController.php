<?php

namespace App\Modules\Permissions\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->hasPermission('permissions.view'), 403);

        $permissions = Permission::orderBy('module')->orderBy('action')->get()
            ->groupBy('module');

        return view('permissions::permissions.index', compact('permissions'));
    }
}
