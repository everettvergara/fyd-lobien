<?php

namespace App\Modules\Media\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\MediaFolder;
use App\Modules\Media\Requests\BulkMediaActionRequest;
use App\Modules\Media\Requests\ReplaceMediaRequest;
use App\Modules\Media\Requests\StoreMediaFolderRequest;
use App\Modules\Media\Requests\StoreMediaRequest;
use App\Modules\Media\Requests\UpdateMediaRequest;
use App\Services\Media\MediaLibraryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function __construct(
        protected MediaLibraryService $media,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Media::class);

        $view = $request->user()
            ? $this->media->preferences->get($request->user()->id, 'view', ['mode' => 'grid'])['mode'] ?? 'grid'
            : 'grid';

        $viewMode = $request->get('view', $view);
        if (! in_array($viewMode, ['grid', 'list'], true)) {
            $viewMode = 'grid';
        }

        return view('media::media.index', [
            'media' => $this->media->browse($request, 24),
            'folders' => $this->media->folders->all(),
            'search' => $request->get('search'),
            'viewMode' => $viewMode,
            'filters' => $request->all(),
        ]);
    }

    public function picker(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Media::class);
        $filters = $request->all();
        $filters['type'] ??= 'image';

        return response()->json([
            'items' => $this->media->picker($filters, (int) $request->get('limit', 48)),
        ]);
    }

    public function store(StoreMediaRequest $request): RedirectResponse|JsonResponse
    {
        if ($request->hasSession()) {
            $request->session()->save();
        }

        $selectedFiles = array_values(array_filter(
            $request->file('files') !== null
                ? $request->file('files')
                : [$request->file('file')],
            fn ($file) => $file !== null,
        ));

        if ($selectedFiles === []) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Please choose at least one file to upload.',
                    'errors' => ['file' => ['Please choose at least one file to upload.']],
                ], 422);
            }

            return back()->with('error', 'Please choose at least one file to upload.');
        }

        [$files, $failed] = $this->partitionUploadFiles($selectedFiles, $request);

        if ($files === []) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No files were uploaded.',
                    'errors' => $failed,
                ], 422);
            }

            return back()
                ->withErrors($failed)
                ->with('error', 'No files were uploaded. Please check the file errors and try again.');
        }

        $uploaded = $this->media->uploadMany(
            $files,
            $request->safe()->except(['file', 'files']),
            $request->user()->id,
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->uploadResultMessage(count($uploaded), count($failed)),
                'uploaded_count' => count($uploaded),
                'requested_count' => count($selectedFiles),
                'failed_count' => count($failed),
                'errors' => $failed,
                'items' => collect($uploaded)->map(fn (Media $media) => $this->media->preview->payload($media))->all(),
            ], $failed === [] ? 201 : 207);
        }

        $count = count($uploaded);
        $redirect = back()->with(
            $failed === [] ? 'success' : 'warning',
            $this->uploadResultMessage($count, count($failed)),
        );

        if ($failed !== []) {
            $redirect->withErrors($failed);
        }

        return $redirect;
    }

    /**
     * @param  array<int, UploadedFile>  $files
     * @return array{0: array<int, UploadedFile>, 1: array<string, array<int, string>>}
     */
    protected function partitionUploadFiles(array $files, StoreMediaRequest $request): array
    {
        $valid = [];
        $failed = [];

        foreach ($files as $index => $file) {
            $key = "files.{$index}";

            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                $failed[$key] = [$this->uploadErrorMessage($file)];
                continue;
            }

            $validator = Validator::make(
                ['file' => $file],
                ['file' => $request->uploadRules()],
            );

            if ($validator->fails()) {
                $failed[$key] = $validator->errors()->all();
                continue;
            }

            $valid[] = $file;
        }

        return [$valid, $failed];
    }

    protected function uploadErrorMessage(mixed $file): string
    {
        if (! $file instanceof UploadedFile) {
            return 'The selected file could not be uploaded.';
        }

        return match ($file->getError()) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The file is larger than the server allows.',
            UPLOAD_ERR_PARTIAL => 'The file was only partially uploaded. Please try again.',
            UPLOAD_ERR_NO_TMP_DIR => 'The server is missing a temporary upload directory.',
            UPLOAD_ERR_CANT_WRITE => 'The server could not write the uploaded file.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the upload.',
            default => 'The file failed to upload.',
        };
    }

    protected function uploadResultMessage(int $uploadedCount, int $failedCount): string
    {
        $uploaded = $uploadedCount === 1 ? '1 file uploaded' : "{$uploadedCount} files uploaded";

        if ($failedCount === 0) {
            return "{$uploaded} successfully.";
        }

        $failed = $failedCount === 1 ? '1 file failed' : "{$failedCount} files failed";

        return "{$uploaded}; {$failed}.";
    }

    public function update(UpdateMediaRequest $request, Media $media): RedirectResponse|JsonResponse
    {
        $this->media->metadata->update($media, $request->validated());
        $this->media->preferences->set($request->user()->id, 'last_folder', ['id' => $request->integer('folder_id') ?: null]);

        if ($request->expectsJson()) {
            return response()->json(['item' => $this->media->preview->payload($media->refresh())]);
        }

        return back()->with('success', 'Asset metadata updated.');
    }

    public function preview(Media $media): JsonResponse
    {
        $this->authorize('view', $media);

        return response()->json($this->media->preview->payload($media));
    }

    public function download(Request $request, Media $media)
    {
        $this->authorize('download', $media);

        return $this->media->downloads->download($media, $request->get('variant'));
    }

    public function archive(Request $request, Media $media): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $media);
        $this->media->deletion->archive($media, $request->user()->id);

        return $this->respond($request, 'Asset archived.');
    }

    public function duplicate(Request $request, Media $media): RedirectResponse|JsonResponse
    {
        $this->authorize('create', Media::class);

        $this->media->bulkActions->apply('copy', [$media->id], [
            'folder_id' => $request->integer('folder_id') ?: $media->folder_id,
        ], $request->user()->id);

        return $this->respond($request, 'Asset duplicated.');
    }

    public function replace(ReplaceMediaRequest $request, Media $media): RedirectResponse|JsonResponse
    {
        $this->media->uploads->replace($media, $request->file('file'), $request->user()->id);

        return $this->respond($request, 'Asset file replaced.');
    }

    public function restore(Request $request, int $media): RedirectResponse|JsonResponse
    {
        $asset = Media::withTrashed()->findOrFail($media);
        $this->authorize('update', $asset);
        $this->media->deletion->restore($asset, $request->user()->id);

        return $this->respond($request, 'Asset restored.');
    }

    public function destroy(Request $request, Media $media): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $media);

        try {
            if ($request->boolean('permanent')) {
                $this->media->deletion->permanentDelete($media, $request->boolean('force'), $request->user()->id);
            } else {
                $this->media->deletion->softDelete($media, $request->boolean('force'), $request->user()->id);
            }
        } catch (\RuntimeException $exception) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], 409);
            }

            return back()->with('error', $exception->getMessage());
        }

        return $this->respond($request, 'Asset deleted.');
    }

    public function storeFolder(StoreMediaFolderRequest $request): RedirectResponse|JsonResponse
    {
        $folder = $this->media->folders->create($request->validated(), $request->user()->id);

        if ($request->expectsJson()) {
            return response()->json(['folder' => $folder], 201);
        }

        return back()->with('success', 'Folder created.');
    }

    public function destroyFolder(Request $request, MediaFolder $folder): RedirectResponse|JsonResponse
    {
        $this->authorize('manageFolders', Media::class);
        $this->media->folders->delete($folder);

        return $this->respond($request, 'Folder deleted.');
    }

    public function bulk(BulkMediaActionRequest $request): RedirectResponse|JsonResponse
    {
        $action = $request->input('action');

        if (in_array($action, ['download', 'zip'], true)) {
            $path = $this->media->downloads->zip($request->input('ids', []));

            if (! $path) {
                return $this->respond($request, 'Unable to prepare ZIP download.', false);
            }

            return Response::download($path)->deleteFileAfterSend();
        }

        $result = $this->media->bulkActions->apply(
            $action,
            $request->input('ids', []),
            $request->validated(),
            $request->user()->id,
        );

        $message = $result['message'] ?? "Processed {$result['processed']} asset(s).";
        if (($result['skipped'] ?? 0) > 0) {
            $message .= " Skipped {$result['skipped']} asset(s).";
        }

        return $this->respond($request, $message);
    }

    public function preference(Request $request): JsonResponse
    {
        $request->validate([
            'key' => ['required', 'string', 'max:100'],
            'value' => ['nullable'],
        ]);

        $this->media->preferences->set($request->user()->id, $request->input('key'), $request->input('value'));

        return response()->json(['ok' => true]);
    }

    protected function respond(Request $request, string $message, bool $success = true): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $success ? 200 : 422);
        }

        return back()->with($success ? 'success' : 'error', $message);
    }
}
