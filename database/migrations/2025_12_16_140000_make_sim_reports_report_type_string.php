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

        // MySQL ENUM will throw "Data truncated" when inserting a value not in the enum list.
        // Over time this project used `report_type` for different concepts (frequency vs category),
        // so we convert it to a plain VARCHAR to accept current UI values (operational, etc.)
        // while keeping older data (daily, monthly, ...).

        // Normalize empty values (possible after invalid ENUM insert attempts)
        try {
            DB::statement("UPDATE sim_reports SET report_type = 'general' WHERE report_type IS NULL OR report_type = ''");
        } catch (\Throwable $e) {
            // ignore: table may be empty or column may not exist in some envs
        }

        // Change to VARCHAR without requiring doctrine/dbal
        try {
            DB::statement("ALTER TABLE sim_reports MODIFY report_type VARCHAR(50) NOT NULL DEFAULT 'general'");
        } catch (\Throwable $e) {
            // If the column doesn't exist in a specific environment, skip
        }
    }

    public function down(): void
    {
        // No safe down migration: converting back to ENUM would risk losing values.
    }
};










