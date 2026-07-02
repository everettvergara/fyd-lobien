<?php

namespace App\Modules\Cache\Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Seeds default public cache settings.
 *
 * @see docs/SEEDING.md
 */
class CacheSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'enabled' => ['true', 'boolean'],
            'ttl_days' => ['1', 'string'],
        ];

        foreach ($defaults as $key => [$value, $type]) {
            Setting::updateOrCreate(
                ['group' => 'cache', 'key' => $key],
                ['value' => $value, 'type' => $type],
            );
        }
    }
}
