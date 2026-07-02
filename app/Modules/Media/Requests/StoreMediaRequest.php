<?php

namespace App\Modules\Media\Requests;

use App\Models\Media;
use App\Services\SettingsService;
use Illuminate\Foundation\Http\FormRequest;

class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Media::class);
    }

    public function rules(): array
    {
        return [
            'file' => ['nullable'],
            'files' => ['nullable', 'array'],
            'files.*' => ['nullable'],
            'folder_id' => ['nullable', 'integer', 'exists:media_folders,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'caption' => ['nullable', 'string'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'copyright' => ['nullable', 'string', 'max:255'],
            'credit' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable'],
        ];
    }

    public function uploadRules(): array
    {
        $settings = app(SettingsService::class);
        $maxKb = min(
            (int) $settings->get('media', 'max_upload_kb', 51200),
            $this->phpSizeToKb(ini_get('upload_max_filesize') ?: '0'),
        );

        return [
            'file',
            "max:{$maxKb}",
            'extensions:jpg,jpeg,png,gif,webp,svg,bmp,ico,mp4,mpeg,mp3,wav,ogg,webm,pdf,txt,zip,doc,docx,xls,xlsx,ppt,pptx',
        ];
    }

    protected function phpSizeToKb(string $value): int
    {
        $value = trim($value);
        $unit = strtolower(substr($value, -1));
        $size = (float) $value;

        $bytes = match ($unit) {
            'g' => $size * 1024 * 1024 * 1024,
            'm' => $size * 1024 * 1024,
            'k' => $size * 1024,
            default => $size,
        };

        return max(1, (int) floor($bytes / 1024));
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('folder_id') === '') {
            $this->merge(['folder_id' => null]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->file('file') === null && $this->file('files') === null) {
                $validator->errors()->add('file', 'Please choose at least one file to upload.');
            }
        });
    }
}
