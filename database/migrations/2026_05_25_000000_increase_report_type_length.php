<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sim_reports')) {
            return;
        }

        // Increase report_type column size to accommodate longer text
        try {
            DB::statement("ALTER TABLE sim_reports MODIFY report_type VARCHAR(255) NOT NULL DEFAULT 'general'");
        } catch (\Throwable $e) {
            // If the column doesn't exist, skip
        }
    }

    public function down(): void
    {
        // No safe down migration - could truncate data
    }
};
