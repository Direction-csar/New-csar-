<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sim_collection_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sim_collection_id')->constrained('sim_collections')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30);
            $table->string('label')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['sim_collection_id', 'status']);
        });

        Schema::create('sim_collection_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sim_collection_id')->constrained('sim_collections')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('accuracy_meters', 8, 2)->nullable();
            $table->decimal('speed_kmh', 8, 2)->nullable();
            $table->unsignedTinyInteger('battery_level')->nullable();
            $table->timestamp('captured_at');
            $table->timestamps();

            $table->index(['sim_collection_id', 'captured_at']);
        });

        Schema::create('sim_data_access_requests', function (Blueprint $table) {
            $table->id();
            $table->string('requester_name');
            $table->string('organization');
            $table->string('role_title')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('request_subject');
            $table->string('requested_scope', 30)->default('aggregated');
            $table->json('requested_data_types')->nullable();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->text('purpose');
            $table->string('status', 30)->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sim_data_access_requests');
        Schema::dropIfExists('sim_collection_positions');
        Schema::dropIfExists('sim_collection_statuses');
    }
};
