<?php

namespace App\Console\Commands;

use App\Services\Module\ModuleManagerService;
use Illuminate\Console\Command;

class ModuleUninstallCommand extends Command
{
    protected $signature = 'module:uninstall {name : The module folder name} {--force : Run without confirmation}';

    protected $description = 'Uninstall a business module and roll back its migrations';

    public function handle(ModuleManagerService $manager): int
    {
        $name = $this->argument('name');

        if (! $this->option('force')) {
            if (! $this->confirm("Uninstall module [{$name}]? This will delete module data.", false)) {
                $this->warn('Uninstall cancelled.');

                return self::FAILURE;
            }

            if ($this->ask('Type the module name to confirm') !== $name) {
                $this->error('Confirmation did not match.');

                return self::FAILURE;
            }
        }

        try {
            $manager->uninstall($name);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Module [{$name}] uninstalled.");

        return self::SUCCESS;
    }
}
