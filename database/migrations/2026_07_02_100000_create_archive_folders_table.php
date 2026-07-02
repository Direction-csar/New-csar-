<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archive_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('direction', 10); // CPM, DFC, DPSE, DRH, DTL
            $table->foreignId('parent_id')->nullable()->constrained('archive_folders')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->index(['direction', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archive_folders');
    }
};
