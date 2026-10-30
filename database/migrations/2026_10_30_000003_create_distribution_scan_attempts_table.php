<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Journal d'audit de TOUTES les tentatives de verification / remise de kit,
 * y compris les refus (code inconnu, agent non affecte, ticket deja utilise...).
 * distribution_scan_logs reste le journal des remises reussies.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('distribution_scan_attempts')) {
            return;
        }

        Schema::create('distribution_scan_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('ticket_id')->nullable()->constrained('distribution_tickets')->nullOnDelete();
            $table->foreignId('planning_id')->nullable()->constrained('distribution_plannings')->nullOnDelete();
            $table->foreignId('beneficiary_id')->nullable()->constrained('distribution_beneficiaries')->nullOnDelete();
            $table->string('action', 20);  // verify | collect
            $table->string('result', 40);  // ok | invalid_code | forbidden | already_used | cancelled | planning_inactive | inconsistent
            $table->string('code', 100)->nullable();
            $table->string('message')->nullable();
            $table->string('device_info')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['ticket_id', 'result']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribution_scan_attempts');
    }
};
