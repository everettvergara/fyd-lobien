<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleCommand extends Command
{
    protected $signature = 'make:module {name : The PascalCase module name}';

    protected $description = 'Scaffold a new installable business module under contrib/';

    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));
        $target = base_path("contrib/{$name}");

        if (File::isDirectory($target)) {
            $this->error("Module directory already exists: contrib/{$name}");

            return self::FAILURE;
        }

        $slug = Str::kebab($name);
        $group = Str::headline($name);
        $viewNamespace = strtolower($name);

        File::makeDirectory("{$target}/Controllers", 0755, true);
        File::makeDirectory("{$target}/Models", 0755, true);
        File::makeDirectory("{$target}/Policies", 0755, true);
        File::makeDirectory("{$target}/Requests", 0755, true);
        File::makeDirectory("{$target}/Services", 0755, true);
        File::makeDirectory("{$target}/Routes", 0755, true);
        File::makeDirectory("{$target}/Views/items", 0755, true);
        File::makeDirectory("{$target}/Database/Migrations", 0755, true);
        File::makeDirectory("{$target}/Database/Seeders", 0755, true);
        File::makeDirectory("{$target}/Tests/Feature", 0755, true);

        File::put("{$target}/module.json", $this->moduleJson($name, $slug, $group));
        File::put("{$target}/Module.php", $this->moduleClass($name, $group));
        File::put("{$target}/README.md", $this->readme($name, $group));
        File::put("{$target}/Routes/admin.php", $this->routes($name));

        $this->info("Created contrib/{$name}");
        $this->line('Next: copy to app/Modules and install via Administration → Modules.');

        return self::SUCCESS;
    }

    protected function moduleJson(string $name, string $slug, string $group): string
    {
        return <<<JSON
{
  "name": "{$name}",
  "slug": "{$slug}",
  "version": "1.0.0",
  "description": "{$group} module for FYD CMS.",
  "group": "{$group}",
  "group_icon": "bi-box",
  "group_sort": 100,
  "author": "",
  "license": "MIT",
  "fyd_cms": ">=0.0.0",
  "requires_core": ["AuditLogs"],
  "autoload": "App\\\\Modules\\\\{$name}",
  "features": []
}

JSON;
    }

    protected function moduleClass(string $name, string $group): string
    {
        return <<<PHP
<?php

namespace App\\Modules\\{$name};

class Module extends \\App\\Framework\\Module
{
    public function name(): string
    {
        return '{$name}';
    }

    public function isInstallable(): bool
    {
        return true;
    }

    public function permissions(): array
    {
        return [];
    }

    public function menuItems(): array
    {
        return [];
    }
}

PHP;
    }

    protected function readme(string $name, string $group): string
    {
        return <<<MD
# {$name}

Installable business module ({$group}).

```bash
cp -r contrib/{$name} app/Modules/{$name}
```

Then install from **Administration → Modules**.

MD;
    }

    protected function routes(string $name): string
    {
        return <<<'PHP'
<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    //
});

PHP;
    }
}
