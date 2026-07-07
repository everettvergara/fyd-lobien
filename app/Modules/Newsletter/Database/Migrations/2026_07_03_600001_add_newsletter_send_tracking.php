<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('newsletter_sends', function (Blueprint $table) {
            $table->string('status', 32)->default('queued')->after('recipient_count');
            $table->timestamp('queued_at')->nullable()->after('status');
            $table->timestamp('started_at')->nullable()->after('queued_at');
            $table->timestamp('completed_at')->nullable()->after('started_at');
            $table->unsignedInteger('sent_count')->default(0)->after('completed_at');
            $table->unsignedInteger('failed_count')->default(0)->after('sent_count');
        });

        Schema::create('newsletter_send_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('newsletter_send_id')->constrained('newsletter_sends')->cascadeOnDelete();
            $table->foreignId('newsletter_subscriber_id')->constrained('newsletter_subscribers')->cascadeOnDelete();
            $table->string('email');
            $table->string('status', 32)->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['newsletter_send_id', 'newsletter_subscriber_id'], 'newsletter_send_recipient_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_send_recipients');

        Schema::table('newsletter_sends', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'queued_at',
                'started_at',
                'completed_at',
                'sent_count',
                'failed_count',
            ]);
        });
    }
};
