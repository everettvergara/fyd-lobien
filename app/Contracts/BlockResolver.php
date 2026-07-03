<?php

namespace App\Contracts;

use App\Modules\PageManager\Models\Page;

interface BlockResolver
{
    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    public function resolve(array $config, Page $page): array;
}
