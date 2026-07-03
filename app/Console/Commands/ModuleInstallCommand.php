<?php

namespace App\Console\Commands;

use App\Services\Module\ModuleManagerService;
use Illuminate\Console\Command;

class ModuleInstallCommand extends Command
{
    protected $signature = 'module:install {name : The module folder name} {--force : Run without confirmation}';

    protected $description = 'Install an installable business module';

    public function handle(ModuleManagerService $manager): int
    {
        $name = $this->argument('name');

        if (! $this->option('force') && ! $this->confirm("Install module [{$name}]?", false)) {
            $this->warn('Install cancelled.');

            return self::FAILURE;
        }

        try {
            $manager->install($name);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Module [{$name}] installed.");

        return self::SUCCESS;
    }
}
