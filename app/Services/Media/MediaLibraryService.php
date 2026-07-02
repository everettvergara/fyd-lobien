<?php

namespace App\Services\Media;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class MediaLibraryService
{
    public function __construct(
        public MediaUploadService $uploads,
        public MediaSearchService $search,
        public MediaMetadataService $metadata,
        public MediaFolderService $folders,
        public MediaUsageService $usage,
        public MediaDownloadService $downloads,
        public MediaDeletionService $deletion,
        public MediaBulkActionService $bulkActions,
        public MediaPreviewService $preview,
        public MediaPreferenceService $preferences,
    ) {}

    public function upload(UploadedFile $file, array $attributes, int $userId): Media
    {
        return $this->uploads->upload($file, $attributes, $userId);
    }

    public function uploadMany(array $files, array $attributes, int $userId): array
    {
        return $this->uploads->uploadMany($files, $attributes, $userId);
    }

    public function browse(Request|array $filters, int $perPage = 24)
    {
        return $this->search->paginate($filters, $perPage);
    }

    public function picker(array $filters = [], int $limit = 48): array
    {
        return $this->search->pickerItems($filters, $limit);
    }
}
