<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personnel', function (Blueprint $table) {
            $columns = [
                'salaire_base' => fn() => $table->decimal('salaire_base', 15, 2)->nullable()->after('poste_actuel'),
                'sursalaire' => fn() => $table->decimal('sursalaire', 15, 2)->nullable()->after('salaire_base'),
                'indemnite_transport' => fn() => $table->decimal('indemnite_transport', 15, 2)->nullable()->after('sursalaire'),
                'indemnite_fonction' => fn() => $table->decimal('indemnite_fonction', 15, 2)->nullable()->after('indemnite_transport'),
                'salaire_brut' => fn() => $table->decimal('salaire_brut', 15, 2)->nullable()->after('indemnite_fonction'),
                'net_a_payer' => fn() => $table->decimal('net_a_payer', 15, 2)->nullable()->after('salaire_brut'),
                'categorie' => fn() => $table->string('categorie')->nullable()->after('net_a_payer'),
                'filiation' => fn() => $table->text('filiation')->nullable()->after('categorie'),
                'date_delivrance_id' => fn() => $table->date('date_delivrance_id')->nullable()->after('filiation'),
                'nombre_epouses' => fn() => $table->integer('nombre_epouses')->nullable()->after('date_delivrance_id'),
                'type_contrat' => fn() => $table->enum('type_contrat', ['CDI', 'CDD'])->default('CDI')->after('nombre_epouses'),
            ];

            foreach ($columns as $name => $callback) {
                if (!Schema::hasColumn('personnel', $name)) {
                    $callback();
                }
            }
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
