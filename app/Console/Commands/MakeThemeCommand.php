<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeThemeCommand extends Command
{
    protected $signature = 'make:theme {name : The theme name (PascalCase or slug)}';

    protected $description = 'Scaffold a new public theme under contrib_themes/ from fyd-default';

    public function handle(): int
    {
        $slug = Str::kebab($this->argument('name'));
        $source = base_path('contrib_themes/fyd-default');
        $target = base_path("contrib_themes/{$slug}");

        if (! is_dir($source)) {
            $this->error('Default theme source not found at contrib_themes/fyd-default.');

            return self::FAILURE;
        }

        if (File::isDirectory($target)) {
            $this->error("Theme directory already exists: contrib_themes/{$slug}");

            return self::FAILURE;
        }

        File::copyDirectory($source, $target);

        $title = Str::headline(str_replace('-', ' ', $slug));
        $manifestPath = "{$target}/theme.json";
        $manifest = json_decode((string) file_get_contents($manifestPath), true) ?? [];

        $manifest['name'] = $title;
        $manifest['slug'] = $slug;
        $manifest['version'] = '1.0.0';
        $manifest['description'] = "{$title} public theme for FYD CMS.";
        $manifest['author'] = '';
        unset($manifest['protected']);

        file_put_contents(
            $manifestPath,
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL
        );

        File::put("{$target}/README.md", <<<MD
# {$title}

Public theme scaffolded from FYD Default.

## Next steps

1. Customize SCSS in `scss/_design-tokens.scss` and Vue files under `js/`.
2. Install to runtime: copy to `themes/{$slug}/` or use **Administration → Public Themes → Install**.
3. Run `npm run build`.
4. Activate the theme in **Administration → Public Themes**.

MD
        );

        $this->info("Created contrib_themes/{$slug}");
        $this->line('Next: install to themes/ and run npm run build.');

        return self::SUCCESS;
    }
}
