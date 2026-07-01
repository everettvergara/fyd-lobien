<?php

namespace App\Modules\Menus\Controllers;

use App\Enums\LinkType;
use App\Enums\MenuLocation;
use App\Http\Controllers\Controller;
use App\Modules\Menus\Models\Menu;
use App\Modules\Menus\Models\MenuItem;
use App\Modules\Menus\Requests\StoreMenuRequest;
use App\Modules\Menus\Requests\UpdateMenuRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MenuController extends Controller
{
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
        $menu = Menu::create($request->validated());
        $this->syncItems($menu, $request->items ?? []);
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
        $menu->update($request->only(['name', 'location']));
        $this->syncItems($menu, $request->items ?? []);
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

    protected function syncItems(Menu $menu, array $items): void
    {
        $menu->allItems()->delete();
        foreach ($items as $index => $item) {
            if (empty($item['title'])) {
                continue;
            }
            MenuItem::create([
                'menu_id' => $menu->id,
                'title' => $item['title'],
                'url' => $item['url'] ?? null,
                'link_type' => $item['link_type'] ?? 'internal',
                'target' => $item['target'] ?? '_self',
                'sort_order' => $index,
            ]);
        }
    }
}
