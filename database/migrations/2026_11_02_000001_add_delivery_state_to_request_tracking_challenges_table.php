<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request_tracking_challenges', function (Blueprint $table) {
            // queued | accepted | failed | skipped | unavailable
            $table->string('delivery_status', 20)->nullable()->after('consumed_at');
            $table->string('delivery_error', 120)->nullable()->after('delivery_status');
            $table->string('provider_message_id', 191)->nullable()->after('delivery_error');
            $table->timestamp('delivery_accepted_at')->nullable()->after('provider_message_id');
        });
    }

    public function down(): void
    {
        Schema::table('request_tracking_challenges', function (Blueprint $table) {
            $table->dropColumn(['delivery_status', 'delivery_error', 'provider_message_id', 'delivery_accepted_at']);
        });
    }
};
