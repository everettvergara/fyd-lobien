<?php

namespace App\Modules\SEO\Services;

class RobotsTxtService
{
    public function render(): string
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            '',
            'Sitemap: '.url('/sitemap.xml'),
        ];

        return implode("\n", $lines)."\n";
    }
}
