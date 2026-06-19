<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->date('event_date')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('status')->default('active'); // active | inactive
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('media_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_event_id')->constrained('media_events')->cascadeOnDelete();
            $table->string('type'); // image | video
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('downloads')->default(0);
            $table->timestamps();
        });

        Schema::create('media_downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_event_id')->constrained('media_events')->cascadeOnDelete();
            $table->foreignId('media_file_id')->nullable()->constrained('media_files')->nullOnDelete();
            $table->string('ip_address', 64)->nullable();
            $table->string('kind')->default('file'); // file | zip_images | zip_videos
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_downloads');
        Schema::dropIfExists('media_files');
        Schema::dropIfExists('media_events');
    }
};
