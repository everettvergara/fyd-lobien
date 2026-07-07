<?php

namespace App\Modules\Careers\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class CareerApplicationStorageService
{
    public const MAX_KB = 10240;

    /**
     * @return array{path: string, original_filename: string}
     */
    public function store(UploadedFile $file): array
    {
        $path = $file->storeAs(
            'career-applications/'.now()->format('Y/m'),
            Str::uuid()->toString().'.pdf',
            'local',
        );

        return [
            'path' => $path,
            'original_filename' => $file->getClientOriginalName(),
        ];
    }
}
