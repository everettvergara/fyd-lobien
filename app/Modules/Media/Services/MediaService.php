<?php

namespace App\Modules\Media\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    public function upload(UploadedFile $file, ?int $folderId, ?string $altText, int $userId): Media
    {
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('media/'.date('Y/m'), $filename, 'public');

        return Media::create([
            'folder_id' => $folderId,
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'disk' => 'public',
            'path' => $path,
            'alt_text' => $altText,
            'uploaded_by' => $userId,
        ]);
    }

    public function uploadMany(array $files, ?int $folderId, ?string $altText, int $userId): array
    {
        return array_map(
            fn ($file) => $this->upload($file, $folderId, $altText, $userId),
            $files,
        );
    }

    public function delete(Media $media): void
    {
        Storage::disk($media->disk)->delete($media->path);
        $media->delete();
    }

    /**
     * @return Collection<int, array{id: int, url: string, filename: string, alt_text: ?string}>
     */
    public function imagesForPicker(?string $search = null, int $limit = 48): Collection
    {
        $query = Media::query()
            ->where('mime_type', 'like', 'image/%')
            ->latest();

        if ($search) {
            $query->where('original_filename', 'like', "%{$search}%");
        }

        return $query->limit($limit)->get()->map(fn (Media $media) => [
            'id' => $media->id,
            'url' => $media->url(),
            'filename' => $media->original_filename,
            'alt_text' => $media->alt_text,
        ]);
    }

    public function find(int $id): ?Media
    {
        return Media::find($id);
    }
}
