<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rh_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('rh_documents', 'version')) {
                $table->unsignedInteger('version')->default(1)->after('statut');
            }
            if (!Schema::hasColumn('rh_documents', 'file_path')) {
                $table->string('file_path')->nullable()->after('version');
            }
            if (!Schema::hasColumn('rh_documents', 'signed_at')) {
                $table->timestamp('signed_at')->nullable()->after('file_path');
            }
            if (!Schema::hasColumn('rh_documents', 'signed_by')) {
                $table->foreignId('signed_by')->nullable()->after('signed_at')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('rh_documents', 'superseded_at')) {
                $table->timestamp('superseded_at')->nullable()->after('signed_by');
            }
            if (!Schema::hasColumn('rh_documents', 'exported_at')) {
                $table->timestamp('exported_at')->nullable()->after('superseded_at');
            }
        });

        if (!Schema::hasTable('rh_salary_imports')) {
            Schema::create('rh_salary_imports', function (Blueprint $table) {
                $table->id();
                $table->string('filename');
                $table->string('sha256', 64);
                $table->string('status', 20)->default('preview'); // preview | applied
                $table->json('summary')->nullable();
                $table->json('rows')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('applied_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('applied_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rh_salary_imports');
        Schema::table('rh_documents', function (Blueprint $table) {
            if (Schema::hasColumn('rh_documents', 'signed_by')) {
                $table->dropConstrainedForeignId('signed_by');
            }
            $table->dropColumn(array_values(array_filter(
                ['version', 'file_path', 'signed_at', 'superseded_at', 'exported_at'],
                fn ($c) => Schema::hasColumn('rh_documents', $c)
            )));
        });
    }
};
