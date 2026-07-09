<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personnel', function (Blueprint $table) {
            $table->decimal('salaire_base', 15, 2)->nullable()->after('poste_actuel');
            $table->decimal('sursalaire', 15, 2)->nullable()->after('salaire_base');
            $table->decimal('indemnite_transport', 15, 2)->nullable()->after('sursalaire');
            $table->decimal('indemnite_fonction', 15, 2)->nullable()->after('indemnite_transport');
            $table->decimal('salaire_brut', 15, 2)->nullable()->after('indemnite_fonction');
            $table->decimal('net_a_payer', 15, 2)->nullable()->after('salaire_brut');
            $table->string('categorie')->nullable()->after('net_a_payer');
            $table->text('filiation')->nullable()->after('categorie');
            $table->date('date_delivrance_id')->nullable()->after('filiation');
            $table->integer('nombre_epouses')->nullable()->after('date_delivrance_id');
            $table->enum('type_contrat', ['CDI', 'CDD'])->default('CDI')->after('nombre_epouses');
        });
    }

    public function down(): void
    {
        Schema::table('personnel', function (Blueprint $table) {
            $table->dropColumn([
                'salaire_base',
                'sursalaire',
                'indemnite_transport',
                'indemnite_fonction',
                'salaire_brut',
                'net_a_payer',
                'categorie',
                'filiation',
                'date_delivrance_id',
                'nombre_epouses',
                'type_contrat',
            ]);
        });
    }
};
