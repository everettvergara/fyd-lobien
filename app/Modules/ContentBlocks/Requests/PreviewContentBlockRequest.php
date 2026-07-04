<?php

namespace App\Modules\ContentBlocks\Requests;

use App\Modules\ContentBlocks\Models\ContentBlock;
use App\Modules\ContentBlocks\Requests\Concerns\ValidatesContentBlockFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PreviewContentBlockRequest extends FormRequest
{
    use ValidatesContentBlockFields;

    public function authorize(): bool
    {
        return $this->user()->can('viewAny', ContentBlock::class);
    }

    public function rules(): array
    {
        $rules = $this->contentBlockRules(updating: $this->route('contentBlock') !== null);

        $rules['key'] = ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'];
        $rules['name'] = ['nullable', 'string', 'max:255'];
        $rules['icon'] = ['nullable', 'string', 'max:100'];
        $rules['status'] = ['nullable', Rule::enum(\App\Enums\ContentStatus::class)];
        $rules['preview_page'] = ['nullable', 'integer', 'min:1', 'max:100'];

        return $rules;
    }
}
