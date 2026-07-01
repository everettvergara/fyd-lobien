<?php

namespace App\Modules\Pages\Services;

use App\Modules\Pages\Models\Page;

class PageSectionService
{
    /**
     * @return array<int, string>
     */
    public function componentTypes(): array
    {
        return [
            'hero_banner',
            'feature_grid',
            'cta',
            'statistics',
            'contact',
            'faq',
        ];
    }

    public function sync(Page $page, array $sections): void
    {
        $page->sections()->delete();

        foreach ($sections as $index => $section) {
            $page->sections()->create([
                'component_type' => $section['component_type'],
                'sort_order' => $index,
                'settings' => $section['settings'] ?? [],
            ]);
        }
    }
}
