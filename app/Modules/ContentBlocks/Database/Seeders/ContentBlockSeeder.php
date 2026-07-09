<?php

namespace App\Modules\ContentBlocks\Database\Seeders;

use App\Enums\ContentStatus;
use App\Modules\ContentBlocks\Enums\ContentBlockFormatter;
use App\Modules\ContentBlocks\Models\ContentBlock;
use Illuminate\Database\Seeder;

class ContentBlockSeeder extends Seeder
{
    /** Bootstrap icon class for the Content Blocks admin sidebar menu item. */
    public const MENU_ICON = 'bi-view-stacked';

    public function run(): void
    {
        foreach ($this->definitions() as $definition) {
            ContentBlock::updateOrCreate(
                ['key' => $definition['key']],
                $definition,
            );
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function definitions(): array
    {
        return [
            $this->latestArticles(),
            $this->featuredPages(),
        ];
    }

    protected function latestArticles(): array
    {
        return [
            'key' => 'latest-articles',
            'name' => "What's New?",
            'summary' => "Don't miss out on the latest news and updates about the Philippine real estate industry",
            'icon' => 'bi-newspaper',
            'status' => ContentStatus::Published,
            'content_types' => ['article'],
            'fields' => [
                ['field' => 'title', 'label' => 'Title', 'class' => 'content-block__title', 'id' => 'content-block-latest-articles-title', 'sort_order' => 0],
                ['field' => 'summary', 'label' => 'Summary', 'class' => 'content-block__summary', 'id' => 'content-block-latest-articles-summary', 'sort_order' => 1],
                ['field' => 'published_at', 'label' => 'Published', 'class' => 'content-block__published-at', 'id' => 'content-block-latest-articles-published-at', 'sort_order' => 2],
                ['field' => 'author.name', 'label' => 'Author', 'class' => 'content-block__author', 'id' => 'content-block-latest-articles-author', 'sort_order' => 3],
                ['field' => 'featured_image', 'label' => 'Featured Image', 'class' => 'content-block__featured-image', 'id' => 'content-block-latest-articles-featured-image', 'sort_order' => 4],
            ],
            'filters' => [],
            'sort_field' => 'published_at',
            'sort_direction' => 'desc',
            'items_per_page' => 4,
            'pagination_enabled' => false,
            'formatter' => ContentBlockFormatter::Unformatted,
            'wrapper_class' => 'content-block content-block--latest-articles',
            'wrapper_id' => 'content-block-latest-articles',
            'settings' => null,
        ];
    }

    protected function featuredPages(): array
    {
        return [
            'key' => 'featured-pages',
            'name' => 'Featured Pages',
            'icon' => 'bi-grid',
            'status' => ContentStatus::Published,
            'content_types' => ['page'],
            'fields' => [
                ['field' => 'title', 'label' => 'Title', 'class' => 'content-block__title', 'id' => 'content-block-featured-pages-title', 'sort_order' => 0],
                ['field' => 'summary', 'label' => 'Summary', 'class' => 'content-block__summary', 'id' => 'content-block-featured-pages-summary', 'sort_order' => 1],
                ['field' => 'featured_image', 'label' => 'Featured Image', 'class' => 'content-block__featured-image', 'id' => 'content-block-featured-pages-featured-image', 'sort_order' => 2],
            ],
            'filters' => [],
            'sort_field' => 'published_at',
            'sort_direction' => 'desc',
            'items_per_page' => 3,
            'pagination_enabled' => false,
            'formatter' => ContentBlockFormatter::Unformatted,
            'wrapper_class' => 'content-block content-block--featured-pages',
            'wrapper_id' => 'content-block-featured-pages',
            'settings' => null,
        ];
    }
}
