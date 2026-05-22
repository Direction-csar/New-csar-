<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sim_reports')) {
            return;
        }

        Schema::table('sim_reports', function (Blueprint $table) {
            if (! Schema::hasColumn('sim_reports', 'category')) {
                $table->string('category')->default('rapport')->after('report_type');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('sim_reports') && Schema::hasColumn('sim_reports', 'category')) {
            Schema::table('sim_reports', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }
    }
};
