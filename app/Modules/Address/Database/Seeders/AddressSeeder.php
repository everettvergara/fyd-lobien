<?php

namespace App\Modules\Address\Database\Seeders;

use App\Modules\Address\Models\City;
use App\Modules\Address\Models\Province;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds Philippine provinces and cities from bundled JSON reference data.
 *
 * @see docs/SEEDING.md
 */
class AddressSeeder extends Seeder
{
    public function run(): void
    {
        $provincesPath = __DIR__.'/../data/provinces.json';
        $locationsPath = __DIR__.'/../data/locations-flat.json';

        if (! file_exists($provincesPath) || ! file_exists($locationsPath)) {
            $this->command?->error('Address data files missing. Expected provinces.json and locations-flat.json in Database/data/.');

            return;
        }

        $provincesData = json_decode(file_get_contents($provincesPath), true);
        $locationsData = json_decode(file_get_contents($locationsPath), true);
        $locations = $locationsData['data'] ?? $locationsData;

        DB::transaction(function () use ($provincesData, $locations) {
            $provinceMap = [];

            foreach ($provincesData as $row) {
                $code = $row['code']['id'] ?? null;
                $name = $row['name']['en'] ?? $row['name']['local'] ?? null;

                if (! $code || ! $name) {
                    continue;
                }

                $province = Province::updateOrCreate(
                    ['code' => $code],
                    ['name' => $name, 'is_active' => true],
                );

                $provinceMap[$code] = $province->id;
            }

            foreach ($locations as $row) {
                if (($row['level'] ?? null) !== 3) {
                    continue;
                }

                $parent = $row['parent'] ?? null;
                $provinceCode = $parent['id'] ?? null;

                if (! $provinceCode || ! isset($provinceMap[$provinceCode])) {
                    continue;
                }

                $name = $row['name']['en'] ?? $row['name']['local'] ?? null;

                if (! $name) {
                    continue;
                }

                City::updateOrCreate(
                    [
                        'province_id' => $provinceMap[$provinceCode],
                        'name' => $name,
                    ],
                    ['is_active' => true],
                );
            }
        });

        $provinceCount = Province::count();
        $cityCount = City::count();

        $this->command?->info("Seeded {$provinceCount} provinces and {$cityCount} cities/municipalities.");
    }
}
