<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('audit_logs', 'data')) {
                $table->json('data')->nullable()->after('user_agent');
            }
            if (!Schema::hasColumn('audit_logs', 'user_id') || Schema::hasColumn('audit_logs', 'user_id')) {
                // S'assurer que user_id accepte null (pour les actions système sans utilisateur)
                $table->unsignedBigInteger('user_id')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (Schema::hasColumn('audit_logs', 'data')) {
                $table->dropColumn('data');
            }
        });
    }
};
