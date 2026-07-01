<?php

namespace App\Modules\Media\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\MediaFolder;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MediaController extends Controller
{
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
            'folders' => MediaFolder::orderBy('name')->get(),
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Media::class);
        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'folder_id' => ['nullable', 'exists:media_folders,id'],
            'alt_text' => ['nullable', 'string', 'max:255'],
        ]);

        $file = $request->file('file');
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('media/'.date('Y/m'), $filename, 'public');

        $media = Media::create([
            'folder_id' => $request->folder_id,
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'disk' => 'public',
            'path' => $path,
            'alt_text' => $request->alt_text,
            'uploaded_by' => $request->user()->id,
        ]);

        ActivityLogger::log('media', 'created', $media);

        return back()->with('success', 'File uploaded successfully.');
    }

    public function destroy(Media $media): RedirectResponse
    {
        $this->authorize('delete', $media);
        Storage::disk($media->disk)->delete($media->path);
        ActivityLogger::log('media', 'deleted', $media);
        $media->delete();

        return back()->with('success', 'File deleted successfully.');
    }
}
