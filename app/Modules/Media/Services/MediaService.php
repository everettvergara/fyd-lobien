<?php

namespace App\Modules\Media\Services;

use App\Models\Media;
use App\Services\Media\MediaLibraryService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class MediaService
{
    public function __construct(
        protected MediaLibraryService $library,
    ) {}

    public function upload(UploadedFile $file, ?int $folderId, ?string $altText, int $userId): Media
    {
        return $this->library->upload($file, [
            'folder_id' => $folderId,
            'alt_text' => $altText,
        ], $userId);
    }

    public function uploadMany(array $files, ?int $folderId, ?string $altText, int $userId): array
    {
        return $this->library->uploadMany($files, [
            'folder_id' => $folderId,
            'alt_text' => $altText,
        ], $userId);
    }

    public function delete(Media $media): void
    {
        $this->library->deletion->softDelete($media, force: true);
    }

    /**
     * @return Collection<int, array{id: int, url: string, filename: string, alt_text: ?string}>
     */
    public function imagesForPicker(?string $search = null, int $limit = 48): Collection
    {
        return collect($this->library->picker([
            'search' => $search,
            'type' => 'image',
        ], $limit));
    }

    public function find(int $id): ?Media
    {
        return Media::find($id);
    }
}
