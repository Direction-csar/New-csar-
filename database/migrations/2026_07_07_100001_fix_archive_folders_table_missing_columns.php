<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('archive_folders', function (Blueprint $table) {
            if (!Schema::hasColumn('archive_folders', 'name')) {
                $table->string('name')->after('id');
            }
            if (!Schema::hasColumn('archive_folders', 'direction')) {
                $table->string('direction', 10)->after('name');
            }
            if (!Schema::hasColumn('archive_folders', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->after('direction')->constrained('archive_folders')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('archive_folders', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('parent_id')->constrained('users');
            }
        });

        Schema::table('archive_folders', function (Blueprint $table) {
            $indexes = collect(Schema::getIndexes('archive_folders'))->pluck('name')->toArray();
            if (!in_array('archive_folders_direction_parent_id_index', $indexes)) {
                $table->index(['direction', 'parent_id']);
            }
        });
    }

    public function down(): void
    {
        // Correctif défensif : pas de rollback destructif.
    }
};
