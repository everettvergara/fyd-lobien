<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('listings')) {
            return;
        }

        Schema::table('listings', function (Blueprint $table) {
            if (! Schema::hasColumn('listings', 'summary')) {
                $table->string('summary', 500)->nullable()->after('name');
            }

            if (! Schema::hasColumn('listings', 'description')) {
                $table->longText('description')->nullable()->after('summary');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('listings')) {
            return;
        }

        Schema::table('listings', function (Blueprint $table) {
            $columns = array_values(array_filter(
                ['summary', 'description'],
                fn (string $column) => Schema::hasColumn('listings', $column),
            ));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
