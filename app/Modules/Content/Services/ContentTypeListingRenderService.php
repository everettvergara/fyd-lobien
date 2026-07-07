<?php

namespace App\Modules\Content\Services;

use App\Modules\Content\Models\ContentType;
use App\Support\PublicSeo;

class ContentTypeListingRenderService
{
    public function __construct(
        protected ContentUrlService $urls,
        protected ContentTypeListingService $listings,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function render(ContentType $type): array
    {
        $queryParam = $this->urls->paginationQueryParam($type->key);
        $pageNumber = max(1, (int) request()->query($queryParam, 1));
        $listing = $this->listings->dto($type, $pageNumber, $queryParam);

        if ($listing === null) {
            abort(404);
        }

        $listingPath = '/'.$type->slug;

        return [
            'page' => [
                'id' => null,
                'path' => $listingPath,
                'title' => $type->label,
                'slug' => $type->slug,
                'summary' => $type->description ?? '',
                'body' => '',
                'featuredImage' => null,
            ],
            'regionOrder' => ['main'],
            'regions' => [
                'main' => [
                    [
                        'id' => 'content-type-listing-0',
                        'type' => 'content-type-listing',
                        'component' => 'ContentTypeListingBlock',
                        'props' => ['listing' => $listing],
                    ],
                ],
            ],
            'seo' => PublicSeo::defaults($type->label, $type->description),
        ];
    }
}
