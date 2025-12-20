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
        if (!Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
                $table->enum('type', ['in', 'out'])->comment('Type de mouvement: entrée ou sortie');
                $table->decimal('quantity', 10, 2)->comment('Quantité du mouvement');
                $table->decimal('quantity_before', 10, 2)->default(0)->comment('Quantité avant le mouvement');
                $table->decimal('quantity_after', 10, 2)->default(0)->comment('Quantité après le mouvement');
                $table->string('reason')->comment('Motif du mouvement');
                $table->string('reference')->unique()->comment('Référence du mouvement');
                $table->integer('reference_number')->comment('Numéro de référence');
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
                
                $table->index(['warehouse_id', 'type']);
                $table->index(['created_at']);
                $table->index(['reference']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
