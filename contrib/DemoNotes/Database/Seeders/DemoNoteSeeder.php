<?php

namespace App\Modules\DemoNotes\Database\Seeders;

use App\Modules\DemoNotes\Models\DemoNote;
use Illuminate\Database\Seeder;

class DemoNoteSeeder extends Seeder
{
    public function run(): void
    {
        $notes = [
            ['title' => 'Welcome', 'body' => 'DemoNotes installed successfully.'],
            ['title' => 'Install worked', 'body' => 'This note was seeded on install.'],
            ['title' => 'Disable me', 'body' => 'Disable the module to hide this group.'],
        ];

        foreach ($notes as $note) {
            DemoNote::firstOrCreate(['title' => $note['title']], $note);
        }
    }
}
