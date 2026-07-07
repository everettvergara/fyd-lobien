<?php

namespace App\Modules\Content\Services;

use App\Modules\Content\Models\Content;
use App\Modules\Content\Models\ContentType;
use App\Support\ContentTypeRegistry;

class ContentUrlService
{
    public function __construct(
        protected ContentTypeRegistry $contentTypes,
    ) {}

    public function pathFor(Content $content): ?string
    {
        $typeSlug = $this->typeSlugFor($content->content_type);

        if ($typeSlug === null) {
            return null;
        }

        return $typeSlug.'/'.$content->slug;
    }

    public function listingPathFor(ContentType $type): ?string
    {
        return $this->normalizeTypeSlug($type->slug);
    }

    public function listingPathForKey(string $contentTypeKey): ?string
    {
        return $this->typeSlugFor($contentTypeKey);
    }

    public function resolveTypeListing(string $path): ?ContentType
    {
        $segment = $this->pathSegment($path);

        if ($segment === null) {
            return null;
        }

        return ContentType::query()
            ->where('is_active', true)
            ->where('slug', $segment)
            ->first();
    }

    public function resolveContentDetail(string $path): ?Content
    {
        $segments = $this->pathSegments($path);

        if ($segments === null || count($segments) !== 2) {
            return null;
        }

        [$typeSlug, $contentSlug] = $segments;

        $type = ContentType::query()
            ->where('is_active', true)
            ->where('slug', $typeSlug)
            ->first();

        if ($type === null) {
            return null;
        }

        return Content::query()
            ->published()
            ->where('content_type', $type->key)
            ->where('slug', $contentSlug)
            ->first();
    }

    public function paginationQueryParam(string $contentTypeKey): string
    {
        return 'ct_'.$contentTypeKey.'_page';
    }

    protected function typeSlugFor(string $contentTypeKey): ?string
    {
        if (! $this->contentTypes->has($contentTypeKey)) {
            return null;
        }

        return $this->normalizeTypeSlug($this->contentTypes->slug($contentTypeKey));
    }

    protected function normalizeTypeSlug(?string $slug): ?string
    {
        return is_string($slug) && $slug !== '' ? $slug : null;
    }

    protected function pathSegment(string $path): ?string
    {
        $segments = $this->pathSegments($path);

        if ($segments === null || count($segments) !== 1) {
            return null;
        }

        return $segments[0];
    }

    /**
     * @return list<string>|null
     */
    protected function pathSegments(string $path): ?array
    {
        $normalized = '/'.ltrim($path, '/');
        $normalized = rtrim($normalized, '/') ?: '/';

        if ($normalized === '/') {
            return null;
        }

        $segments = array_values(array_filter(explode('/', trim($normalized, '/')), fn (string $segment) => $segment !== ''));

        return $segments === [] ? null : $segments;
    }
}
