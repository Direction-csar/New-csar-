<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('archives', function (Blueprint $table) {
            if (!Schema::hasColumn('archives', 'reference')) {
                $table->string('reference')->unique()->after('id');
            }
            if (!Schema::hasColumn('archives', 'title')) {
                $table->string('title')->after('reference');
            }
            if (!Schema::hasColumn('archives', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            if (!Schema::hasColumn('archives', 'direction')) {
                $table->string('direction', 10)->after('description');
            }
            if (!Schema::hasColumn('archives', 'folder_id')) {
                $table->foreignId('folder_id')->nullable()->after('direction')->constrained('archive_folders');
            }
            if (!Schema::hasColumn('archives', 'annee')) {
                $table->year('annee')->default(now()->year)->after('folder_id');
            }
            if (!Schema::hasColumn('archives', 'file_path')) {
                $table->string('file_path')->after('annee');
            }
            if (!Schema::hasColumn('archives', 'file_name')) {
                $table->string('file_name')->after('file_path');
            }
            if (!Schema::hasColumn('archives', 'file_size')) {
                $table->bigInteger('file_size')->default(0)->after('file_name');
            }
            if (!Schema::hasColumn('archives', 'mime_type')) {
                $table->string('mime_type')->nullable()->after('file_size');
            }
            if (!Schema::hasColumn('archives', 'page_count')) {
                $table->integer('page_count')->nullable()->after('mime_type');
            }
            if (!Schema::hasColumn('archives', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('page_count')->constrained('users');
            }
            if (!Schema::hasColumn('archives', 'deleted_by')) {
                $table->foreignId('deleted_by')->nullable()->after('created_by')->constrained('users');
            }
            if (!Schema::hasColumn('archives', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('archives', function (Blueprint $table) {
            $indexes = collect(Schema::getIndexes('archives'))->pluck('name')->toArray();

            if (!in_array('archives_direction_annee_folder_id_index', $indexes)) {
                $table->index(['direction', 'annee', 'folder_id']);
            }
            if (!in_array('archives_direction_deleted_at_index', $indexes)) {
                $table->index(['direction', 'deleted_at']);
            }
        });
    }

    public function down(): void
    {
        // Correctif défensif : pas de rollback destructif.
    }
};
