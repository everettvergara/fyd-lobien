<?php

namespace App\Modules\Content\Services;

use App\Modules\Content\Models\Content;
use App\Modules\Content\Models\ContentType;
use App\Support\PublicContent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ContentTypeListingService
{
    public const PER_PAGE = 10;

    public function __construct(
        protected ContentUrlService $urls,
    ) {}

    public function dto(ContentType $type, int $page = 1, ?string $queryParam = null): ?array
    {
        $listingPath = $this->urls->listingPathFor($type);

        if ($listingPath === null) {
            return null;
        }

        $queryParam ??= 'page';
        $page = max(1, $page);

        $paginator = Content::query()
            ->with('featuredImage')
            ->published()
            ->where('content_type', $type->key)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE, ['*'], $queryParam, $page);

        return [
            'contentType' => [
                'key' => $type->key,
                'label' => $type->label,
                'slug' => $type->slug,
                'listingPath' => $listingPath,
            ],
            'items' => collect($paginator->items())
                ->map(fn (Content $content) => PublicContent::contentCard($content))
                ->values()
                ->all(),
            'pagination' => $this->paginationMeta($paginator, $queryParam),
        ];
    }

    /**
     * @return array{currentPage: int, lastPage: int, perPage: int, total: int, queryParam: string}
     */
    protected function paginationMeta(LengthAwarePaginator $paginator, string $queryParam): array
    {
        return [
            'currentPage' => $paginator->currentPage(),
            'lastPage' => $paginator->lastPage(),
            'perPage' => $paginator->perPage(),
            'total' => $paginator->total(),
            'queryParam' => $queryParam,
        ];
    }
}
