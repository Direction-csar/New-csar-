<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_tracking_challenges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('public_request_id')->nullable()->index();
            $table->string('session_hash', 64);
            $table->string('code_hash', 64);
            $table->string('email_hash', 64)->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('request_notification_deliveries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('public_request_id')->index();
            $table->string('from_status');
            $table->string('to_status');
            $table->string('state')->default('prepared')->index();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Rollback requires an explicit data preservation plan.');
    }
};
