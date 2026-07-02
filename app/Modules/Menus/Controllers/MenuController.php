<?php

namespace App\Modules\Menus\Controllers;

use App\Enums\LinkType;
use App\Enums\MenuLocation;
use App\Framework\Admin\List\AdminBulkActionService;
use App\Http\Controllers\Controller;
use App\Modules\Cache\Services\PublicCacheService;
use App\Modules\Menus\Models\Menu;
use App\Modules\Menus\Requests\StoreMenuRequest;
use App\Modules\Menus\Requests\UpdateMenuRequest;
use App\Modules\Menus\Services\MenuAdminListService;
use App\Modules\Menus\Services\MenuService;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function __construct(
        protected MenuAdminListService $menuList,
        protected MenuService $menus,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Menu::class);

        return view('menus::menus.index', [
            'list' => $this->menuList->result($request),
        ]);
    }

    public function bulk(Request $request, AdminBulkActionService $bulkActions): RedirectResponse
    {
        $this->authorize('viewAny', Menu::class);

        $count = $bulkActions->execute($this->menuList->definition(), $request);
        app(PublicCacheService::class)->clearAll();

        return back()->with('success', "{$count} menu(s) updated successfully.");
    }

    public function create(): View
    {
        $this->authorize('create', Menu::class);

        return view('menus::menus.create', ['locations' => MenuLocation::cases()]);
    }

    public function store(StoreMenuRequest $request): RedirectResponse
    {
        $menu = $this->menus->create($request->validated(), $request->items ?? []);
        app(PublicCacheService::class)->clearAll();
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
        app(PublicCacheService::class)->clearAll();
        ActivityLogger::log('menus', 'updated', $menu);

        return redirect()->route('admin.menus.index')->with('success', 'Menu updated successfully.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $this->authorize('delete', $menu);
        ActivityLogger::log('menus', 'deleted', $menu);
        $menu->delete();
        app(PublicCacheService::class)->clearAll();

        return redirect()->route('admin.menus.index')->with('success', 'Menu deleted successfully.');
    }
}
