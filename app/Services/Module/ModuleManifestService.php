<?php

namespace App\Services\Module;

use App\Framework\Module;
use App\Models\Permission;
use App\Support\CmsVersion;
use InvalidArgumentException;

class ModuleManifestService
{
    public function validateForInstall(Module $module): void
    {
        if (! $module->isInstallable()) {
            throw new InvalidArgumentException("Module [{$module->name()}] is not installable.");
        }

        $manifest = $module->manifest();

        if ($manifest === []) {
            throw new InvalidArgumentException("Module [{$module->name()}] is missing module.json.");
        }

        if (empty($manifest['group'])) {
            throw new InvalidArgumentException("Module [{$module->name()}] must define group in module.json.");
        }

        if (! $this->isCompatible($manifest['fyd_cms'] ?? null)) {
            throw new InvalidArgumentException("Module [{$module->name()}] is not compatible with this CMS version.");
        }
    }

    public function isCompatible(?string $constraint): bool
    {
        if ($constraint === null || $constraint === '') {
            return true;
        }

        $version = CmsVersion::string();

        if (preg_match('/^>=\s*(.+)$/', trim($constraint), $matches)) {
            return version_compare($version, trim($matches[1]), '>=');
        }

        return true;
    }

    /**
     * @return array<int, string>
     */
    public function requiredCoreModules(Module $module): array
    {
        $requires = $module->manifest()['requires_core'] ?? [];

        return is_array($requires) ? array_values($requires) : [];
    }
}
