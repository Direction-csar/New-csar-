<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archives', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('direction', 10); // CPM, DFC, DPSE, DRH, DTL
            $table->foreignId('folder_id')->nullable()->constrained('archive_folders');
            $table->year('annee')->default(now()->year);
            $table->string('file_path');
            $table->string('file_name');
            $table->bigInteger('file_size');
            $table->string('mime_type')->nullable();
            $table->integer('page_count')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('deleted_by')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();
            $table->index(['direction', 'annee', 'folder_id']);
            $table->index(['direction', 'deleted_at']);
            $table->index('reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archives');
    }
};
