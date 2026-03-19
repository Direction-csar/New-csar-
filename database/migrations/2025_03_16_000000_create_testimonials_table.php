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
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('organization')->nullable();
            $table->enum('type', ['general', 'mission'])->default('general'); // Type de témoignage
            $table->string('mission_location')->nullable(); // Lieu de la mission
            $table->date('mission_date')->nullable(); // Date de la mission
            $table->text('message');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->integer('rating')->nullable(); // Note de 1 à 5
            $table->boolean('is_featured')->default(false); // Pour mettre en avant certains témoignages
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
