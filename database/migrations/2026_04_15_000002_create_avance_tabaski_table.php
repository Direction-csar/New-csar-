<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avance_tabaski', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents_tabaski')->onDelete('cascade');
            $table->enum('montant', ['100000', '150000', '200000']);
            $table->string('ip_address')->nullable();
            $table->timestamp('date_inscription')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avance_tabaski');
    }
};
