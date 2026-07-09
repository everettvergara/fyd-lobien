<?php

namespace App\Modules\PageManager\Blocks;

use App\Contracts\BlockResolver;
use App\Modules\PageManager\Models\Page;

class ContactMapBlockResolver implements BlockResolver
{
    private const DEFAULT_EMBED_URL = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3409.330608884188!2d121.04874297450714!3d14.547904085932247!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397c909e6a4a1b1%3A0xeb5bff922a557eb2!2sHigh%20Street%20South%20Corporate%20Plaza%20Tower%201!5e1!3m2!1sen!2sph!4v1736730881493!5m2!1sen!2sph';

    public function resolve(array $config, Page $page): array
    {
        return [
            'embed_url' => (string) ($config['embed_url'] ?? self::DEFAULT_EMBED_URL),
            'title' => (string) ($config['title'] ?? 'Lobien Realty Group office location'),
        ];
    }
}
