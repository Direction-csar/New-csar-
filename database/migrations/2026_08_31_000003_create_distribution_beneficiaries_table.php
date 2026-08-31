<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribution_beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planning_id')->constrained('distribution_plannings')->cascadeOnDelete();
            $table->string('full_name');
            $table->string('phone')->nullable();
            $table->string('cni')->nullable();
            $table->text('address')->nullable();
            $table->string('category')->nullable();
            $table->decimal('quantity_kg', 10, 2)->default(0);
            $table->boolean('is_vulnerable')->default(false);
            $table->boolean('is_pregnant')->default(false);
            $table->boolean('is_elderly')->default(false);
            $table->boolean('is_disabled')->default(false);
            $table->enum('status', ['pending', 'validated', 'ticket_issued', 'kit_collected'])->default('pending');
            $table->datetime('validated_at')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['planning_id', 'phone']);
            $table->index(['planning_id', 'cni']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribution_beneficiaries');
    }
};
