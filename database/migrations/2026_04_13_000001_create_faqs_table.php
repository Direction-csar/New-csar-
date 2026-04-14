<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->text('answer');
            $table->string('category')->default('usager'); // usager, bailleur, general
            $table->string('locale', 5)->default('fr');
            $table->boolean('is_published')->default(true);
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->index(['category', 'locale', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
