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
        Schema::create('chiffres_cles', function (Blueprint $table) {
            $table->id();
            $table->string('icone', 100); // Classe Font Awesome (ex: fa-users)
            $table->string('titre', 255); // Titre du chiffre (ex: Agents recensés)
            $table->string('valeur', 100); // Valeur affichée (ex: 137, 79 tonnes, 50+)
            $table->string('description', 255)->nullable(); // Description courte
            $table->string('couleur', 50)->default('#3b82f6'); // Couleur du badge/icône
            $table->enum('statut', ['Actif', 'Inactif'])->default('Actif'); // Visibilité
            $table->integer('ordre')->default(0); // Ordre d'affichage
            $table->timestamps();
            
            // Index pour performances
            $table->index(['statut', 'ordre']);
        });
        
        // Insérer les données par défaut
        DB::table('chiffres_cles')->insert([
            [
                'icone' => 'fa-users',
                'titre' => 'Agents recensés',
                'valeur' => '137',
                'description' => 'Agents mobilisés sur le terrain',
                'couleur' => '#3b82f6',
                'statut' => 'Actif',
                'ordre' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'icone' => 'fa-warehouse',
                'titre' => 'Magasins de stockage',
                'valeur' => '71',
                'description' => 'Entrepôts de stockage actifs',
                'couleur' => '#10b981',
                'statut' => 'Actif',
                'ordre' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'icone' => 'fa-weight',
                'titre' => 'Capacité de stockage',
                'valeur' => '79 000 tonnes',
                'description' => 'Capacité totale de stockage',
                'couleur' => '#f59e0b',
                'statut' => 'Actif',
                'ordre' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'icone' => 'fa-calendar-alt',
                'titre' => 'Années d\'expérience',
                'valeur' => '50+',
                'description' => 'Années au service de la sécurité alimentaire',
                'couleur' => '#8b5cf6',
                'statut' => 'Actif',
                'ordre' => 4,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'icone' => 'fa-map-marked-alt',
                'titre' => 'Régions couvertes',
                'valeur' => '14',
                'description' => 'Régions du Sénégal couvertes',
                'couleur' => '#ec4899',
                'statut' => 'Actif',
                'ordre' => 5,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'icone' => 'fa-clipboard-check',
                'titre' => 'Demandes traitées',
                'valeur' => '15 598',
                'description' => 'Demandes traitées depuis la création',
                'couleur' => '#06b6d4',
                'statut' => 'Actif',
                'ordre' => 6,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'icone' => 'fa-star',
                'titre' => 'Taux de satisfaction',
                'valeur' => '94.5%',
                'description' => 'Satisfaction des bénéficiaires',
                'couleur' => '#eab308',
                'statut' => 'Actif',
                'ordre' => 7,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chiffres_cles');
    }
};
