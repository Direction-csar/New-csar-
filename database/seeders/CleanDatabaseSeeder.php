<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CleanDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Nettoyer toutes les tables
        $this->cleanDatabase();
        
        // Créer les données essentielles
        $this->createEssentialData();
        
        $this->command->info('✅ Base de données nettoyée et initialisée avec succès');
    }
    
    /**
     * Nettoyer toutes les tables
     */
    private function cleanDatabase()
    {
        $tables = [
            'users', 'demandes', 'rapports', 'actualites', 'partenaires', 
            'entrepots', 'newsletter', 'contacts', 'public_contents',
            'statistics', 'news', 'warehouses', 'technical_partners',
            'contact_messages', 'newsletter_subscribers', 'public_requests'
        ];
        
        foreach ($tables as $table) {
            try {
                DB::table($table)->truncate();
                $this->command->info("🧹 Table {$table} nettoyée");
            } catch (\Exception $e) {
                $this->command->warn("⚠️ Impossible de nettoyer la table {$table}: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Créer les données essentielles
     */
    private function createEssentialData()
    {
        // Créer ou mettre à jour l'utilisateur admin (évite "Duplicate entry" si users non truncaté)
        $roleId = \App\Models\Role::where('name', 'admin')->first()?->id;
        User::updateOrCreate(
            ['email' => 'admin@csar.sn'],
            array_filter([
                'name' => 'Administrateur CSAR',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'role_id' => $roleId,
                'is_active' => true,
            ])
        );
        
        // Créer les contenus publics de base
        $this->createPublicContent();
        
        // Créer les statistiques de base
        $this->createBaseStatistics();
        
        $this->command->info('✅ Données essentielles créées');
    }
    
    /**
     * Créer le contenu public de base
     */
    private function createPublicContent()
    {
        $contents = [
            // Contenu À propos
            ['section' => 'about', 'key_name' => 'title', 'value' => 'À propos du CSAR', 'type' => 'text'],
            ['section' => 'about', 'key_name' => 'description', 'value' => 'Le Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR) est une institution publique créée par décret n° 2024-1234 du 15 juillet 2024, placée sous la tutelle du Ministère de la Famille et des Solidarités. Notre mission est d\'assurer la sécurité alimentaire et de renforcer la résilience des populations face aux crises alimentaires et climatiques.', 'type' => 'html'],
            
            // Contact
            ['section' => 'contact', 'key_name' => 'email', 'value' => 'contact@csar.sn', 'type' => 'email'],
            ['section' => 'contact', 'key_name' => 'phone', 'value' => '+221 33 123 45 67', 'type' => 'phone'],
            ['section' => 'contact', 'key_name' => 'address', 'value' => 'Dakar, Sénégal', 'type' => 'text'],
            ['section' => 'contact', 'key_name' => 'hours', 'value' => 'Lun-Ven: 8h-17h', 'type' => 'text'],
        ];
        
        foreach ($contents as $content) {
            DB::table('public_contents')->insert(array_merge($content, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
    
    /**
     * Créer les statistiques de base (colonnes: key, title, description, value, section, order, is_active)
     */
    private function createBaseStatistics()
    {
        $statistics = [
            ['key' => 'agents_count', 'title' => 'Agents', 'description' => 'Agents de terrain', 'value' => '0', 'section' => 'about', 'order' => 1, 'is_active' => true],
            ['key' => 'warehouses_count', 'title' => 'Entrepôts', 'description' => 'Entrepôts actifs', 'value' => '0', 'section' => 'about', 'order' => 2, 'is_active' => true],
            ['key' => 'capacity_count', 'title' => 'Capacité', 'description' => 'Tonnes de capacité', 'value' => '0', 'section' => 'about', 'order' => 3, 'is_active' => true],
            ['key' => 'experience_count', 'title' => 'Années', 'description' => "Années d'expérience", 'value' => '0', 'section' => 'about', 'order' => 4, 'is_active' => true],
        ];

        foreach ($statistics as $stat) {
            if (DB::table('statistics')->where('key', $stat['key'])->exists()) {
                continue;
            }
            DB::table('statistics')->insert(array_merge($stat, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}

