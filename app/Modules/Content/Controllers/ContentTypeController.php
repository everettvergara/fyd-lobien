<?php

namespace App\Modules\Content\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Content\Models\ContentType;
use App\Modules\Content\Requests\StoreContentTypeRequest;
use App\Modules\Content\Requests\UpdateContentTypeRequest;
use App\Modules\Content\Services\ContentTypeAdminListService;
use App\Services\ActivityLogger;
use App\Support\ContentTypeRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContentTypeController extends Controller
{
    public function __construct(
        protected ContentTypeAdminListService $contentTypeList,
        protected ContentTypeRegistry $contentTypes,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ContentType::class);

        return view('content::content-types.index', [
            'list' => $this->contentTypeList->result($request),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', ContentType::class);

        return view('content::content-types.create');
    }

    public function store(StoreContentTypeRequest $request): RedirectResponse
    {
        $contentType = ContentType::create($request->validated());
        $this->contentTypes->forgetCache();
        ActivityLogger::log('content-types', 'created', $contentType, ['label' => $contentType->label]);

        return redirect()->route('admin.content-types.index')
            ->with('success', 'Content type created successfully.');
    }

    public function edit(ContentType $contentType): View
    {
        $this->authorize('update', $contentType);

        return view('content::content-types.edit', compact('contentType'));
    }

    public function update(UpdateContentTypeRequest $request, ContentType $contentType): RedirectResponse
    {
        $contentType->update($request->validated());
        $this->contentTypes->forgetCache();
        ActivityLogger::log('content-types', 'updated', $contentType, ['label' => $contentType->label]);

        return redirect()->route('admin.content-types.index')
            ->with('success', 'Content type updated successfully.');
    }

    public function destroy(ContentType $contentType): RedirectResponse
    {
        $this->authorize('delete', $contentType);

        if ($contentType->contents()->exists()) {
            return back()->with('error', 'Cannot delete a content type that has entries.');
        }

        ActivityLogger::log('content-types', 'deleted', $contentType, ['label' => $contentType->label]);
        $contentType->delete();
        $this->contentTypes->forgetCache();

        return redirect()->route('admin.content-types.index')
            ->with('success', 'Content type deleted successfully.');
    }
}
