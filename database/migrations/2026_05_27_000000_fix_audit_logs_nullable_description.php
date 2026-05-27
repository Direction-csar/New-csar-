<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (Schema::hasColumn('audit_logs', 'description')) {
                $table->text('description')->nullable()->change();
            }
            if (!Schema::hasColumn('audit_logs', 'level')) {
                $table->string('level')->nullable()->after('action');
            }
            if (!Schema::hasColumn('audit_logs', 'status')) {
                $table->string('status')->nullable()->after('level');
            }
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (Schema::hasColumn('audit_logs', 'description')) {
                $table->text('description')->nullable(false)->change();
            }
            if (Schema::hasColumn('audit_logs', 'level')) {
                $table->dropColumn('level');
            }
            if (Schema::hasColumn('audit_logs', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
