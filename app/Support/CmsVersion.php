<?php

namespace App\Support;

class CmsVersion
{
    public static function major(): int
    {
        return (int) config('cms.version.major', 0);
    }

    public static function minor(): int
    {
        return (int) config('cms.version.minor', 0);
    }

    public static function release(): int
    {
        return (int) config('cms.version.release', 0);
    }

    public static function string(): string
    {
        return sprintf('%d.%d.%d', self::major(), self::minor(), self::release());
    }

    public static function templateName(): string
    {
        return (string) config('cms.template_name', 'FYD Laravel Bootstrap CMS');
    }

    /**
     * @return array{major: int, minor: int, release: int, version: string, template_name: string}
     */
    public static function info(): array
    {
        return [
            'major' => self::major(),
            'minor' => self::minor(),
            'release' => self::release(),
            'version' => self::string(),
            'template_name' => self::templateName(),
        ];
    }
}
