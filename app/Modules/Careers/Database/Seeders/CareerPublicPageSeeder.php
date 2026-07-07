<?php

namespace App\Modules\Careers\Database\Seeders;

use App\Modules\Careers\Services\CareerPageSyncService;
use Illuminate\Database\Seeder;

class CareerPublicPageSeeder extends Seeder
{
    public function run(): void
    {
        app(CareerPageSyncService::class)->syncIndexPage();
    }
}
