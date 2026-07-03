<?php

namespace App\Modules\Careers\Database\Seeders;

use App\Modules\Careers\Models\CareerJob;
use Illuminate\Database\Seeder;

class DemoCareerSeeder extends Seeder
{
    public function run(): void
    {
        $jobs = [
            [
                'slug' => 'senior-web-developer',
                'title' => 'Senior Web Developer',
                'department' => 'Engineering',
                'location' => 'Manila, PH',
                'employment_type' => 'full_time',
                'salary_range' => 'Competitive',
                'summary' => 'Build and maintain modern web applications for our CMS platform.',
                'description' => '<p>We are looking for an experienced web developer to join our product team.</p>',
                'requirements' => '<ul><li>5+ years PHP/Laravel experience</li><li>Strong Vue.js skills</li></ul>',
                'sort_order' => 10,
            ],
            [
                'slug' => 'marketing-coordinator',
                'title' => 'Marketing Coordinator',
                'department' => 'Marketing',
                'location' => 'Remote',
                'employment_type' => 'remote',
                'salary_range' => null,
                'summary' => 'Support campaigns and content initiatives across digital channels.',
                'description' => '<p>Help coordinate marketing programs and track campaign performance.</p>',
                'requirements' => '<ul><li>Excellent communication skills</li><li>Experience with CMS platforms</li></ul>',
                'sort_order' => 20,
            ],
        ];

        foreach ($jobs as $data) {
            CareerJob::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    ...$data,
                    'status' => CareerJob::STATUS_PUBLISHED,
                    'published_at' => now(),
                    'closing_date' => now()->addMonths(2)->toDateString(),
                ],
            );
        }
    }
}
