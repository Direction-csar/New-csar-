<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribution_plannings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('distribution_events')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('planned_quota_kg', 12, 2)->default(0);
            $table->decimal('executed_kg', 12, 2)->default(0);
            $table->integer('expected_beneficiaries')->default(0);
            $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('draft');
            $table->date('distribution_date')->nullable();
            $table->string('location')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribution_plannings');
    }
};
