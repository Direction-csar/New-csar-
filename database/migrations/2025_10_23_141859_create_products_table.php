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
        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('type')->nullable();
                $table->string('unit')->default('kg');
                $table->text('description')->nullable();
                $table->decimal('unit_price', 10, 2)->default(0);
                $table->string('category')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->index(['is_active', 'name']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
