<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_lookups', function (Blueprint $table) {
            $table->id();
            $table->string('group', 100);
            $table->string('value', 100);
            $table->string('label');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['group', 'value']);
            $table->index(['group', 'is_active', 'sort_order']);
        });

        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('summary', 500)->nullable();
            $table->longText('description')->nullable();
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->string('brgy')->nullable();
            $table->text('address')->nullable();
            $table->decimal('office_rental_rate', 12, 2)->nullable();
            $table->decimal('total_area_size', 12, 2)->nullable();
            $table->decimal('unit_market_size', 12, 2)->nullable();
            $table->decimal('retail_market_rate', 12, 2)->nullable();
            $table->string('completion_status')->nullable();
            $table->timestamps();
        });

        Schema::create('listing_specs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('listings')->cascadeOnDelete();
            $table->string('developer')->nullable();
            $table->string('grade')->nullable();
            $table->unsignedSmallInteger('completion_year')->nullable();
            $table->string('completion_qtr')->nullable();
            $table->string('no_of_floors')->nullable();
            $table->string('no_of_basement')->nullable();
            $table->string('density_ratio')->nullable();
            $table->string('parking_allocation')->nullable();
            $table->string('floor_to_ceiling_height')->nullable();
            $table->decimal('gross_leasable_area', 12, 2)->nullable();
            $table->decimal('typical_floor_area', 12, 2)->nullable();
            $table->decimal('typical_retail_floor_area', 12, 2)->nullable();
            $table->string('floor_efficiency')->nullable();
            $table->timestamps();

            $table->unique('listing_id');
        });

        Schema::create('listing_building_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('listings')->cascadeOnDelete();
            $table->string('operating_hours')->nullable();
            $table->string('ac_system')->nullable();
            $table->string('no_of_lifts_passenger')->nullable();
            $table->string('no_of_lifts_service')->nullable();
            $table->string('telco')->nullable();
            $table->string('backup_power')->nullable();
            $table->timestamps();

            $table->unique('listing_id');
        });

        Schema::create('listing_other_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('listings')->cascadeOnDelete();
            $table->string('peza_accreditation')->nullable();
            $table->text('sustainability')->nullable();
            $table->boolean('other_info_visible')->default(true);
            $table->timestamps();

            $table->unique('listing_id');
        });

        Schema::create('listing_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('listings')->cascadeOnDelete();
            $table->string('floor')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('area_size', 12, 2)->nullable();
            $table->decimal('rental', 12, 2)->nullable();
            $table->string('handover_condition')->nullable();
            $table->string('availability')->nullable();
            $table->string('bedrooms')->nullable();
            $table->decimal('selling_price', 12, 2)->nullable();
            $table->string('property_type')->nullable();
            $table->boolean('for_lease')->default(false);
            $table->boolean('for_sale')->default(false);
            $table->text('last_remarks')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['listing_id', 'sort_order']);
        });

        Schema::create('listing_remarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('listings')->cascadeOnDelete();
            $table->foreignId('listing_unit_id')->nullable()->constrained('listing_units')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('comment');
            $table->timestamp('remarked_at');
            $table->timestamps();

            $table->index(['listing_id', 'remarked_at']);
        });

        Schema::create('listing_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('listings')->cascadeOnDelete();
            $table->string('fee_type');
            $table->decimal('fee', 12, 2)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['listing_id', 'sort_order']);
        });

        Schema::create('listing_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('listings')->cascadeOnDelete();
            $table->string('asset_type');
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['listing_id', 'asset_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_assets');
        Schema::dropIfExists('listing_fees');
        Schema::dropIfExists('listing_remarks');
        Schema::dropIfExists('listing_units');
        Schema::dropIfExists('listing_other_infos');
        Schema::dropIfExists('listing_building_services');
        Schema::dropIfExists('listing_specs');
        Schema::dropIfExists('listings');
        Schema::dropIfExists('listing_lookups');
    }
};
