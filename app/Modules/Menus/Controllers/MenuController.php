<?php

namespace App\Modules\Menus\Controllers;

use App\Enums\LinkType;
use App\Enums\MenuLocation;
use App\Http\Controllers\Controller;
use App\Modules\Menus\Models\Menu;
use App\Modules\Menus\Requests\StoreMenuRequest;
use App\Modules\Menus\Requests\UpdateMenuRequest;
use App\Modules\Menus\Services\MenuService;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function __construct(
        protected MenuService $menus,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Menu::class);
        $menus = Menu::withCount('allItems')->get();

        return view('menus::menus.index', compact('menus'));
    }

    public function create(): View
    {
        $this->authorize('create', Menu::class);

        return view('menus::menus.create', ['locations' => MenuLocation::cases()]);
    }

    public function store(StoreMenuRequest $request): RedirectResponse
    {
        $menu = $this->menus->create($request->validated(), $request->items ?? []);
        ActivityLogger::log('menus', 'created', $menu);

        return redirect()->route('admin.menus.index')->with('success', 'Menu created successfully.');
    }

    public function edit(Menu $menu): View
    {
        $this->authorize('update', $menu);
        $menu->load('allItems');

        return view('menus::menus.edit', [
            'menu' => $menu,
            'locations' => MenuLocation::cases(),
            'linkTypes' => LinkType::cases(),
        ]);
    }

    public function update(UpdateMenuRequest $request, Menu $menu): RedirectResponse
    {
        $this->menus->update($menu, $request->only(['name', 'location']), $request->items ?? []);
        ActivityLogger::log('menus', 'updated', $menu);

        return redirect()->route('admin.menus.index')->with('success', 'Menu updated successfully.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $this->authorize('delete', $menu);
        ActivityLogger::log('menus', 'deleted', $menu);
        $menu->delete();

        return redirect()->route('admin.menus.index')->with('success', 'Menu deleted successfully.');
    }
}
