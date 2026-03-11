<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sim_regions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code', 20)->nullable()->unique();
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('sim_departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sim_region_id')->constrained('sim_regions')->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['sim_region_id', 'name']);
        });

        Schema::create('sim_markets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sim_department_id')->constrained('sim_departments')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->nullable()->unique();
            $table->string('commune')->nullable();
            $table->string('market_type', 50);
            $table->string('market_day', 50)->nullable();
            $table->boolean('is_permanent')->default(false);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['sim_department_id', 'name']);
            $table->index(['market_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sim_markets');
        Schema::dropIfExists('sim_departments');
        Schema::dropIfExists('sim_regions');
    }
};
