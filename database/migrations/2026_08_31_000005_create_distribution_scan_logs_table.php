<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribution_scan_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('distribution_tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('action', ['scan', 'collect', 'cancel'])->default('scan');
            $table->text('notes')->nullable();
            $table->string('device_info')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribution_scan_logs');
    }
};
