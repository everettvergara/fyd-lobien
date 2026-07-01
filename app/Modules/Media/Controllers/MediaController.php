<?php

namespace App\Modules\Media\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Modules\Media\Requests\StoreMediaRequest;
use App\Modules\Media\Services\MediaService;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function __construct(
        protected MediaService $mediaService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Media::class);

        $query = Media::with('uploader')->latest();
        if ($search = $request->get('search')) {
            $query->where('original_filename', 'like', "%{$search}%");
        }
        if ($folderId = $request->get('folder')) {
            $query->where('folder_id', $folderId);
        }

        return view('media::media.index', [
            'media' => $query->paginate(24),
            'folders' => \App\Models\MediaFolder::orderBy('name')->get(),
            'search' => $search,
        ]);
    }

    public function picker(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Media::class);

        return response()->json([
            'items' => $this->mediaService->imagesForPicker(
                $request->get('search'),
                (int) $request->get('limit', 48)
            ),
        ]);
    }

    public function store(StoreMediaRequest $request): RedirectResponse
    {
        $media = $this->mediaService->upload(
            $request->file('file'),
            $request->integer('folder_id') ?: null,
            $request->input('alt_text'),
            $request->user()->id,
        );

        ActivityLogger::log('media', 'created', $media);

        return back()->with('success', 'File uploaded successfully.');
    }

    public function destroy(Media $media): RedirectResponse
    {
        $this->authorize('delete', $media);

        ActivityLogger::log('media', 'deleted', $media);
        $this->mediaService->delete($media);

        return back()->with('success', 'File deleted successfully.');
    }
}
