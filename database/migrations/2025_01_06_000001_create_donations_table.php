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
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method'); // wave, orange_money, credit_card, etc.
            $table->string('payment_status')->default('pending'); // pending, success, failed
            $table->string('transaction_id')->nullable();
            $table->string('currency', 3)->default('XOF');
            $table->boolean('is_anonymous')->default(false);
            $table->text('message')->nullable();
            $table->string('donation_type')->default('single'); // single, monthly
            $table->string('frequency')->nullable(); // monthly, quarterly, yearly
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['payment_status', 'created_at']);
            $table->index(['payment_method', 'payment_status']);
            $table->index('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
