<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
            $table->string('public_page_path')->nullable()->after('published_to_public');
            $table->unique(['city', 'slug'], 'listings_city_slug_unique');
        });

        if (! Schema::hasTable('listings')) {
            return;
        }

        $used = [];

        DB::table('listings')
            ->orderBy('id')
            ->get(['id', 'code', 'name', 'city'])
            ->each(function ($row) use (&$used) {
                $base = Str::slug((string) ($row->name ?: $row->code ?: 'listing-'.$row->id));
                if ($base === '') {
                    $base = 'listing-'.$row->id;
                }

                $cityKey = (string) ($row->city ?? '');
                $slug = $base;
                $suffix = 2;

                while (isset($used[$cityKey][$slug])) {
                    $slug = $base.'-'.$suffix;
                    $suffix++;
                }

                $used[$cityKey][$slug] = true;

                DB::table('listings')->where('id', $row->id)->update(['slug' => $slug]);
            });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropUnique('listings_city_slug_unique');
            $table->dropColumn(['slug', 'public_page_path']);
        });
    }
};
