<?php

namespace App\Modules\Banners\Requests;

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
            'key' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:banners,key'],
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'rich_text' => ['nullable', 'string'],
            'template_id' => ['nullable', 'exists:banner_templates,id'],
            'desktop_image_id' => ['nullable', 'exists:media,id'],
            'tablet_image_id' => ['nullable', 'exists:media,id'],
            'mobile_image_id' => ['nullable', 'exists:media,id'],
            'background_image_id' => ['nullable', 'exists:media,id'],
            'background_video_id' => ['nullable', 'exists:media,id'],
            'poster_image_id' => ['nullable', 'exists:media,id'],
            'button_text' => ['nullable', 'string', 'max:255'],
            'button_url' => ['nullable', 'string', 'max:500'],
            'button_target' => ['nullable', Rule::in(['_self', '_blank'])],
            'button_style' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer'],
            'status' => ['required', Rule::enum(ContentStatus::class)],
            'column_ratio' => ['nullable', Rule::in(['50/50', '40/60', '60/40'])],
            'effect' => ['nullable', Rule::in(['none', 'fade', 'slide', 'zoom', 'ken_burns', 'scale', 'blur_reveal'])],
            'animation_speed' => ['nullable', 'integer', 'min:0', 'max:30000'],
            'delay' => ['nullable', 'integer', 'min:0', 'max:30000'],
            'loop' => ['nullable', 'boolean'],
            'autoplay' => ['nullable', 'boolean'],
            'slides' => ['nullable', 'array'],
            'slides.*.name' => ['nullable', 'string', 'max:255'],
            'slides.*.blocks' => ['nullable', 'array'],
            'slides.*.blocks.*.region' => ['nullable', 'string', 'max:80'],
            'slides.*.blocks.*.type' => ['nullable', 'string', 'max:80'],
            'slides.*.blocks.*.headline' => ['nullable', 'string', 'max:255'],
            'slides.*.blocks.*.subheading' => ['nullable', 'string', 'max:255'],
            'slides.*.blocks.*.description' => ['nullable', 'string'],
            'slides.*.blocks.*.rich_text' => ['nullable', 'string'],
            'slides.*.blocks.*.buttons' => ['nullable', 'array'],
            'slides.*.blocks.*.buttons.*.label' => ['nullable', 'string', 'max:255'],
            'slides.*.blocks.*.buttons.*.url' => ['nullable', 'string', 'max:500'],
            'slides.*.blocks.*.buttons.*.target' => ['nullable', Rule::in(['_self', '_blank'])],
            'slides.*.blocks.*.buttons.*.style' => ['nullable', 'string', 'max:50'],
            'slides.*.media' => ['nullable', 'array'],
            'slides.*.media.*.media_id' => ['nullable', 'exists:media,id'],
        ];
    }
}
