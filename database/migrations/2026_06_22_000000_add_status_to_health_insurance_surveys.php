<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('health_insurance_surveys', function (Blueprint $table) {
            $table->enum('status', ['draft', 'confirmed'])->default('draft')->after('submitted_at');
            $table->timestamp('expires_at')->nullable()->after('status');
            $table->index('status');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('health_insurance_surveys', function (Blueprint $table) {
            $table->dropColumn(['status', 'expires_at']);
        });
    }
};
