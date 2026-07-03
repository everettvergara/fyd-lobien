<?php

namespace App\Console\Commands;

use App\Services\Module\ModuleManagerService;
use Illuminate\Console\Command;

class ModuleDisableCommand extends Command
{
    protected $signature = 'module:disable {name : The module folder name} {--force : Run without confirmation}';

    protected $description = 'Disable an installed business module';

    public function handle(ModuleManagerService $manager): int
    {
        $name = $this->argument('name');

        if (! $this->option('force') && ! $this->confirm("Disable module [{$name}]? Data will be preserved.", false)) {
            $this->warn('Disable cancelled.');

            return self::FAILURE;
        }

        try {
            $manager->disable($name);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Module [{$name}] disabled.");

        return self::SUCCESS;
    }
}
