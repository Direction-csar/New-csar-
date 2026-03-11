<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sim_product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->nullable()->unique();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('sim_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sim_product_category_id')->constrained('sim_product_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->nullable()->unique();
            $table->string('unit', 50)->default('kg');
            $table->string('origin_type', 20)->default('both');
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['sim_product_category_id', 'name']);
            $table->index(['origin_type', 'is_active']);
        });

        Schema::create('sim_collector_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sim_market_id')->constrained('sim_markets')->cascadeOnDelete();
            $table->foreignId('collector_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['collector_id', 'is_active']);
            $table->index(['supervisor_id', 'is_active']);
        });

        Schema::create('sim_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sim_market_id')->constrained('sim_markets')->cascadeOnDelete();
            $table->foreignId('collector_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('collected_on');
            $table->string('market_day', 50)->nullable();
            $table->string('status', 30)->default('draft');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->unsignedTinyInteger('progress_percentage')->default(0);
            $table->boolean('has_live_tracking')->default(false);
            $table->text('observations')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->unique(['sim_market_id', 'collector_id', 'collected_on']);
            $table->index(['status', 'collected_on']);
            $table->index(['collector_id', 'status']);
        });

        Schema::create('sim_collection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sim_collection_id')->constrained('sim_collections')->cascadeOnDelete();
            $table->foreignId('sim_product_id')->constrained('sim_products')->cascadeOnDelete();
            $table->decimal('quantity_observed', 12, 2)->nullable();
            $table->decimal('price_producer', 12, 2)->nullable();
            $table->decimal('price_retail', 12, 2)->nullable();
            $table->decimal('price_wholesale', 12, 2)->nullable();
            $table->decimal('offer_quantity', 12, 2)->nullable();
            $table->decimal('stock_quantity', 12, 2)->nullable();
            $table->string('origin_label', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['sim_collection_id', 'sim_product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sim_collection_items');
        Schema::dropIfExists('sim_collections');
        Schema::dropIfExists('sim_collector_assignments');
        Schema::dropIfExists('sim_products');
        Schema::dropIfExists('sim_product_categories');
    }
};
