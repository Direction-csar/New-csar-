<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projets', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description');
            $table->string('statut')->default('actif'); // actif, termine, suspendu
            $table->string('icon')->nullable();
            $table->string('image')->nullable();
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->string('region')->nullable();
            $table->string('budget')->nullable();
            $table->boolean('lien_sim')->default(false);
            $table->boolean('is_published')->default(true);
            $table->integer('position')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['is_published', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projets');
    }
};
