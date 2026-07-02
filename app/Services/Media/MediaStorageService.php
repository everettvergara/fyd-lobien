<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaStorageService
{
    public function store(UploadedFile $file, string $disk = 'public', ?string $directory = null): array
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $filename = Str::uuid().($extension ? ".{$extension}" : '');
        $directory ??= 'media/'.now()->format('Y/m');
        $path = $file->storeAs($directory, $filename, $disk);

        return [
            'disk' => $disk,
            'storage_provider' => $this->providerForDisk($disk),
            'path' => $path,
            'filename' => $filename,
            'extension' => $extension,
            'visibility' => $disk === 'public' ? 'public' : 'private',
        ];
    }

    public function delete(string $disk, string $path): void
    {
        Storage::disk($disk)->delete($path);
    }

    public function copy(string $sourceDisk, string $sourcePath, string $targetDisk, string $targetPath): void
    {
        $stream = Storage::disk($sourceDisk)->readStream($sourcePath);

        if ($stream === false) {
            return;
        }

        Storage::disk($targetDisk)->put($targetPath, $stream);

        if (is_resource($stream)) {
            fclose($stream);
        }
    }

    public function url(string $disk, string $path): string
    {
        return Storage::disk($disk)->url($path);
    }

    public function providerForDisk(string $disk): string
    {
        return config("filesystems.disks.{$disk}.driver", 'local');
    }
}
