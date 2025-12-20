<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sim_reports', function (Blueprint $table) {
            // Ajouter les colonnes manquantes
            if (!Schema::hasColumn('sim_reports', 'report_type')) {
                $table->enum('report_type', ['financial', 'operational', 'inventory', 'personnel', 'general'])->default('general')->after('description');
            }
            if (!Schema::hasColumn('sim_reports', 'period_start')) {
                $table->date('period_start')->nullable()->after('report_type');
            }
            if (!Schema::hasColumn('sim_reports', 'period_end')) {
                $table->date('period_end')->nullable()->after('period_start');
            }
            if (!Schema::hasColumn('sim_reports', 'status')) {
                $table->enum('status', ['pending', 'generating', 'completed', 'published', 'failed', 'scheduled'])->default('pending')->after('period_end');
            }
            if (!Schema::hasColumn('sim_reports', 'document_file')) {
                $table->string('document_file')->nullable()->after('status');
            }
            if (!Schema::hasColumn('sim_reports', 'cover_image')) {
                $table->string('cover_image')->nullable()->after('document_file');
            }
            if (!Schema::hasColumn('sim_reports', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('is_public');
            }
            if (!Schema::hasColumn('sim_reports', 'generated_by')) {
                $table->unsignedBigInteger('generated_by')->nullable()->after('created_by');
            }
            if (!Schema::hasColumn('sim_reports', 'generated_at')) {
                $table->timestamp('generated_at')->nullable()->after('generated_by');
            }
            if (!Schema::hasColumn('sim_reports', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable()->after('generated_at');
            }
            if (!Schema::hasColumn('sim_reports', 'download_count')) {
                $table->integer('download_count')->default(0)->after('scheduled_at');
            }
            if (!Schema::hasColumn('sim_reports', 'view_count')) {
                $table->integer('view_count')->default(0)->after('download_count');
            }
            if (!Schema::hasColumn('sim_reports', 'file_size')) {
                $table->bigInteger('file_size')->nullable()->after('view_count');
            }
            if (!Schema::hasColumn('sim_reports', 'metadata')) {
                $table->json('metadata')->nullable()->after('file_size');
            }
            
            // Renommer file_url en document_file si nécessaire
            if (Schema::hasColumn('sim_reports', 'file_url') && !Schema::hasColumn('sim_reports', 'document_file')) {
                $table->renameColumn('file_url', 'document_file');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sim_reports', function (Blueprint $table) {
            // Supprimer les colonnes ajoutées
            $table->dropColumn([
                'report_type', 'period_start', 'period_end', 'status',
                'document_file', 'cover_image', 'created_by', 'generated_by',
                'generated_at', 'scheduled_at', 'download_count', 'view_count',
                'file_size', 'metadata'
            ]);
            
            // Supprimer les index
            $table->dropIndex(['status', 'is_public']);
            $table->dropIndex(['report_type', 'status']);
        });
    }
};
