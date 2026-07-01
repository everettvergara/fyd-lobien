<?php

namespace App\Modules\Banners\Requests;

use App\Enums\BannerPlacement;
use App\Enums\BannerType;
use App\Enums\ContentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBannerRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->can('create', \App\Modules\Banners\Models\Banner::class); }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::enum(BannerType::class)],
            'placement' => ['required', Rule::enum(BannerPlacement::class)],
            'button_text' => ['nullable', 'string', 'max:255'],
            'button_url' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer'],
            'status' => ['required', Rule::enum(ContentStatus::class)],
            'published_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
        ];
    }
}
