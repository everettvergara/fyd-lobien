<?php

namespace App\Modules\DemoNotes\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\DemoNotes\Models\DemoNote;
use App\Modules\DemoNotes\Requests\StoreDemoNoteRequest;
use App\Modules\DemoNotes\Requests\UpdateDemoNoteRequest;
use App\Modules\DemoNotes\Services\DemoNoteAdminListService;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DemoNoteController extends Controller
{
    public function __construct(
        protected DemoNoteAdminListService $list,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', DemoNote::class);

        return view('demonotes::demo-notes.index', [
            'list' => $this->list->result($request),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', DemoNote::class);

        return view('demonotes::demo-notes.create');
    }

    public function store(StoreDemoNoteRequest $request): RedirectResponse
    {
        $note = DemoNote::create($request->validated());

        ActivityLogger::log('demo_notes', 'created', $note);

        return redirect()->route('admin.demo-notes.index')->with('success', 'Demo note created.');
    }

    public function edit(DemoNote $demoNote): View
    {
        $this->authorize('update', $demoNote);

        return view('demonotes::demo-notes.edit', compact('demoNote'));
    }

    public function update(UpdateDemoNoteRequest $request, DemoNote $demoNote): RedirectResponse
    {
        $demoNote->update($request->validated());

        ActivityLogger::log('demo_notes', 'updated', $demoNote);

        return redirect()->route('admin.demo-notes.index')->with('success', 'Demo note updated.');
    }

    public function destroy(DemoNote $demoNote): RedirectResponse
    {
        $this->authorize('delete', $demoNote);

        ActivityLogger::log('demo_notes', 'deleted', $demoNote);

        $demoNote->delete();

        return redirect()->route('admin.demo-notes.index')->with('success', 'Demo note deleted.');
    }
}
