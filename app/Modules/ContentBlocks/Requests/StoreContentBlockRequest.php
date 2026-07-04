<?php

namespace App\Modules\ContentBlocks\Requests;

use App\Modules\ContentBlocks\Models\ContentBlock;
use App\Modules\ContentBlocks\Requests\Concerns\ValidatesContentBlockFields;
use Illuminate\Foundation\Http\FormRequest;

class StoreContentBlockRequest extends FormRequest
{
    use ValidatesContentBlockFields;

    public function authorize(): bool
    {
        return $this->user()->can('create', ContentBlock::class);
    }

    public function rules(): array
    {
        return $this->contentBlockRules();
    }
}
