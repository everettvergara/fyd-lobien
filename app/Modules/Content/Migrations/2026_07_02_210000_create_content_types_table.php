<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_types', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->string('label');
            $table->text('description')->nullable();
            $table->string('icon', 100)->default('bi-file-earmark');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $sort = 0;
        foreach (config('content-types', []) as $key => $type) {
            DB::table('content_types')->insert([
                'key' => $key,
                'label' => $type['label'] ?? $key,
                'description' => $type['description'] ?? null,
                'icon' => $type['icon'] ?? 'bi-file-earmark',
                'sort_order' => $sort++,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('content_types');
    }
};
