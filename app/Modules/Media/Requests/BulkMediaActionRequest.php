<?php

namespace App\Modules\Media\Requests;

use App\Models\Media;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkMediaActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return match ($this->input('action')) {
            'delete' => $this->user()->can('bulkDelete', Media::class),
            'download', 'zip' => $this->user()->can('bulkDownload', Media::class),
            default => $this->user()->hasPermission('media.edit'),
        };
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in([
                'delete',
                'download',
                'zip',
                'move',
                'change_folder',
                'add_tags',
                'remove_tags',
                'archive',
                'restore',
                'copy',
            ])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:media,id'],
            'folder_id' => ['nullable', 'exists:media_folders,id'],
            'tags' => ['nullable'],
            'force' => ['nullable', 'boolean'],
        ];
    }
}
