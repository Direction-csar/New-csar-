<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribution_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiary_id')->constrained('distribution_beneficiaries')->cascadeOnDelete();
            $table->foreignId('planning_id')->constrained('distribution_plannings')->cascadeOnDelete();
            $table->string('ticket_code')->unique();
            $table->string('qr_token')->unique();
            $table->enum('status', ['issued', 'scanned', 'collected', 'cancelled'])->default('issued');
            $table->datetime('issued_at');
            $table->datetime('scanned_at')->nullable();
            $table->datetime('collected_at')->nullable();
            $table->foreignId('scanned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('scan_location')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribution_tickets');
    }
};
