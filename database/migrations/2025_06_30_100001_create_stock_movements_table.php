<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->date('movement_date');
            $table->string('label'); // Libellé : "Réception lot 2167", "Distribution", etc.
            $table->string('position')->nullable(); // Position dans le magasin : "A1", "B2"
            $table->decimal('entry_sacs', 15, 2)->default(0);
            $table->decimal('entry_kg', 15, 2)->default(0);
            $table->decimal('exit_sacs', 15, 2)->default(0);
            $table->decimal('exit_kg', 15, 2)->default(0);
            $table->decimal('balance_sacs', 15, 2)->default(0); // Solde calculé
            $table->decimal('balance_kg', 15, 2)->default(0);   // Solde calculé
            $table->text('observation')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete(); // Magasinier
            $table->string('reference_number')->unique(); // Référence traçable
            $table->string('qr_code_data')->nullable(); // Données encodées dans le QR
            $table->enum('movement_type', ['entry', 'exit', 'transfer', 'adjustment', 'report'])->default('entry');
            $table->enum('status', ['draft', 'validated', 'rejected'])->default('draft');
            $table->enum('sync_status', ['pending', 'synced', 'error'])->default('pending');
            $table->timestamp('validated_at')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['warehouse_id', 'movement_date']);
            $table->index('reference_number');
            $table->index('sync_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
