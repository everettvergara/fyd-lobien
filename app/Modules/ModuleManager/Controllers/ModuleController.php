<?php

namespace App\Modules\ModuleManager\Controllers;

use App\Framework\ModuleManager;
use App\Http\Controllers\Controller;
use App\Services\Module\ModuleManagerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModuleController extends Controller
{
    public function __construct(
        protected ModuleManagerService $modules,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', ModuleManager::class);

        return view('modulemanager::modules.index', [
            'modules' => $this->modules->listForAdmin(),
        ]);
    }

    public function install(Request $request, string $name): RedirectResponse
    {
        $this->authorize('install', ModuleManager::class);

        $request->validate([
            'confirm' => ['accepted'],
        ]);

        try {
            $this->modules->install($name, auth()->id());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Module [{$name}] installed successfully.");
    }

    public function disable(Request $request, string $name): RedirectResponse
    {
        $this->authorize('disable', ModuleManager::class);

        $request->validate([
            'confirm' => ['accepted'],
        ]);

        try {
            $this->modules->disable($name, auth()->id());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Module [{$name}] disabled.");
    }

    public function enable(string $name): RedirectResponse
    {
        $this->authorize('enable', ModuleManager::class);

        try {
            $this->modules->enable($name, auth()->id());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Module [{$name}] enabled.");
    }

    public function uninstall(Request $request, string $name): RedirectResponse
    {
        $this->authorize('uninstall', ModuleManager::class);

        $request->validate([
            'confirm' => ['accepted'],
            'module_name' => ['required', 'in:'.$name],
        ]);

        try {
            $this->modules->uninstall($name, auth()->id());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Module [{$name}] uninstalled.");
    }
}
