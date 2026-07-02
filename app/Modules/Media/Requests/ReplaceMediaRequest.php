<?php

namespace App\Modules\Media\Requests;

use App\Services\SettingsService;
use Illuminate\Foundation\Http\FormRequest;

class ReplaceMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('replace', $this->route('media'));
    }

    public function rules(): array
    {
        $settings = app(SettingsService::class);
        $maxKb = min(
            (int) $settings->get('media', 'max_upload_kb', 51200),
            $this->phpSizeToKb(ini_get('upload_max_filesize') ?: '0'),
        );

        return [
            'file' => [
                'required',
                'file',
                "max:{$maxKb}",
                'extensions:jpg,jpeg,png,gif,webp,svg,bmp,ico,mp4,mpeg,mp3,wav,ogg,webm,pdf,txt,zip,doc,docx,xls,xlsx,ppt,pptx',
            ],
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
}
