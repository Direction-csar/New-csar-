<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('speeches', function (Blueprint $table) {
            $table->id();
            $table->string('author');
            $table->string('function')->nullable();
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->date('date')->nullable();
            $table->string('location')->nullable();
            $table->text('summary')->nullable();
            $table->string('portrait')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('speeches');
    }
};
