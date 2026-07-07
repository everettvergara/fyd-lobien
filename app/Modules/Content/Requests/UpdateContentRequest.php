<?php

namespace App\Modules\Content\Requests;

use App\Enums\ContentStatus;
use App\Modules\Content\Models\Content;
use App\Modules\Content\Requests\Concerns\ValidatesContentPagePath;
use App\Modules\PageManager\Models\Page;
use App\Rules\PdfMedia;
use App\Support\ContentTypeRegistry;
use App\Support\HtmlSanitizer;
use App\Support\SlugValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContentRequest extends FormRequest
{
    use ValidatesContentPagePath;

    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('content'));
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('body')) {
            $this->merge(['body' => HtmlSanitizer::clean($this->input('body'))]);
        }

        $this->mergePublicPagePathForValidation();
    }

    public function rules(): array
    {
        return [
            'content_type' => ['required', 'string', Rule::in(app(ContentTypeRegistry::class)->keys())],
            'title' => ['required', 'string', 'max:255'],
            'slug' => SlugValidation::rules(Rule::unique('contents', 'slug')->ignore($this->route('content')->id)),
            'summary' => ['nullable', 'string'],
            'body' => ['nullable', 'string'],
            'url_link' => ['nullable', 'url', 'max:2048'],
            'attachment_id' => ['nullable', 'exists:media,id', new PdfMedia],
            'featured_image_id' => ['nullable', 'exists:media,id'],
            'gallery_media_ids' => ['nullable', 'array'],
            'gallery_media_ids.*' => ['exists:media,id'],
            'status' => ['required', Rule::enum(ContentStatus::class)],
            'published_at' => ['nullable', 'date'],
            '_public_page_path' => $this->publicPagePathRules(),
        ];
    }

    protected function syncedPageIdToIgnore(): ?int
    {
        $content = $this->route('content');

        if (! $content instanceof Content) {
            return null;
        }

        $path = $content->public_page_path;

        if (! is_string($path) || $path === '') {
            return null;
        }

        return Page::findByPath($path)?->id;
    }
}
