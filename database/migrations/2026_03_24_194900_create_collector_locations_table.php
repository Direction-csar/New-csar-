<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collector_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collector_id')->constrained('sim_collectors')->onDelete('cascade');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('accuracy', 8, 2)->nullable();
            $table->enum('status', ['active', 'collecting', 'paused', 'offline'])->default('offline');
            $table->string('current_market')->nullable();
            $table->integer('collections_today')->default(0);
            $table->timestamp('last_activity_at');
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['collector_id', 'created_at']);
            $table->index('last_activity_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collector_locations');
    }
};
