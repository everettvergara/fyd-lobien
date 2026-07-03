<?php

namespace App\Console\Commands;

use App\Services\Module\ModuleManagerService;
use Illuminate\Console\Command;

class ModuleEnableCommand extends Command
{
    protected $signature = 'module:enable {name : The module folder name}';

    protected $description = 'Enable a disabled business module';

    public function handle(ModuleManagerService $manager): int
    {
        $name = $this->argument('name');

        try {
            $manager->enable($name);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Module [{$name}] enabled.");

        return self::SUCCESS;
    }
}
