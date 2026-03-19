<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sim_collectors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email')->unique();
            $table->string('phone', 20);
            $table->string('password_hash');
            $table->json('assigned_zones')->nullable(); // Zones assignées
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->timestamp('last_sync')->nullable();
            $table->integer('total_collections')->default(0);
            $table->timestamps();
            
            $table->index(['status', 'last_sync']);
        });

        Schema::create('sim_mobile_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collector_id')->constrained('sim_collectors')->onDelete('cascade');
            $table->unsignedBigInteger('market_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('retail_price', 10, 2)->nullable();
            $table->decimal('wholesale_price', 10, 2)->nullable();
            $table->date('collection_date');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->json('photos')->nullable(); // URLs des photos
            $table->json('metadata')->nullable(); // Données supplémentaires
            $table->enum('sync_status', ['pending', 'synced', 'failed'])->default('pending');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            
            $table->index(['collector_id', 'collection_date']);
            $table->index(['sync_status', 'collection_date']);
        });

        // Ajouter les foreign keys après la création des tables
        Schema::table('sim_mobile_collections', function (Blueprint $table) {
            $table->foreign('market_id')->references('id')->on('markets')->onDelete('set null');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
        });

        Schema::create('sim_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collector_id')->constrained('sim_collectors')->onDelete('cascade');
            $table->integer('data_count');
            $table->enum('sync_type', ['full', 'incremental', 'manual']);
            $table->enum('status', ['success', 'failed', 'partial']);
            $table->text('error_message')->nullable();
            $table->json('synced_data_ids')->nullable();
            $table->timestamp('sync_started_at');
            $table->timestamp('sync_completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['collector_id', 'sync_started_at']);
            $table->index(['status', 'sync_started_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sim_sync_logs');
        Schema::dropIfExists('sim_mobile_collections');
        Schema::dropIfExists('sim_collectors');
    }
};
