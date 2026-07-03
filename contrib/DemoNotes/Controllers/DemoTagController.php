<?php

namespace App\Modules\DemoNotes\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\DemoNotes\Models\DemoTag;
use App\Modules\DemoNotes\Requests\StoreDemoTagRequest;
use App\Modules\DemoNotes\Requests\UpdateDemoTagRequest;
use App\Modules\DemoNotes\Services\DemoTagAdminListService;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DemoTagController extends Controller
{
    public function __construct(
        protected DemoTagAdminListService $list,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', DemoTag::class);

        return view('demonotes::demo-tags.index', [
            'list' => $this->list->result($request),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', DemoTag::class);

        return view('demonotes::demo-tags.create');
    }

    public function store(StoreDemoTagRequest $request): RedirectResponse
    {
        $tag = DemoTag::create($request->validated());

        ActivityLogger::log('demo_tags', 'created', $tag);

        return redirect()->route('admin.demo-tags.index')->with('success', 'Demo tag created.');
    }

    public function edit(DemoTag $demoTag): View
    {
        $this->authorize('update', $demoTag);

        return view('demonotes::demo-tags.edit', compact('demoTag'));
    }

    public function update(UpdateDemoTagRequest $request, DemoTag $demoTag): RedirectResponse
    {
        $demoTag->update($request->validated());

        ActivityLogger::log('demo_tags', 'updated', $demoTag);

        return redirect()->route('admin.demo-tags.index')->with('success', 'Demo tag updated.');
    }

    public function destroy(DemoTag $demoTag): RedirectResponse
    {
        $this->authorize('delete', $demoTag);

        ActivityLogger::log('demo_tags', 'deleted', $demoTag);

        $demoTag->delete();

        return redirect()->route('admin.demo-tags.index')->with('success', 'Demo tag deleted.');
    }
}
