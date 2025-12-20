<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Demande;
use App\Models\PublicRequest;
use App\Models\Notification;
use App\Models\Message;
use App\Models\StockMovement;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Création des données de démonstration...');
        
        // Récupérer les utilisateurs et entrepôts existants
        $admin = User::where('email', 'admin@csar.sn')->first();
        $warehouses = Warehouse::all();
        
        if (!$admin) {
            $this->command->error('❌ Utilisateur admin non trouvé. Exécutez d\'abord UserSeeder.');
            return;
        }
        
        if ($warehouses->isEmpty()) {
            $this->command->error('❌ Aucun entrepôt trouvé. Exécutez d\'abord WarehouseSeeder.');
            return;
        }
        
        // 1. Créer des demandes (table demandes)
        $this->createDemandes();
        
        // 2. Créer des demandes publiques (table public_requests)
        $this->createPublicRequests($admin);
        
        // 3. Créer des notifications
        $this->createNotifications($admin);
        
        // 4. Créer des messages
        $this->createMessages($admin);
        
        // 5. Créer des mouvements de stock
        $this->createStockMovements($warehouses);
        
        $this->command->info('✅ Données de démonstration créées avec succès !');
    }
    
    private function createDemandes()
    {
        $this->command->info('   📋 Création des demandes...');
        
        $demandes = [
            [
                'nom' => 'Diop',
                'prenom' => 'Amadou',
                'email' => 'amadou.diop@example.com',
                'telephone' => '+221 77 123 45 67',
                'objet' => 'Demande d\'aide alimentaire urgente',
                'description' => 'Notre village a besoin d\'aide alimentaire d\'urgence suite à la sécheresse.',
                'type_demande' => 'aide_alimentaire',
                'statut' => 'en_attente',
                'region' => 'Dakar',
                'commune' => 'Parcelles Assainies',
                'adresse' => 'Unité 12, Parcelles Assainies',
                'latitude' => 14.7645,
                'longitude' => -17.3946,
                'tracking_code' => 'CSAR-' . strtoupper(substr(md5(uniqid()), 0, 8)),
                'created_at' => Carbon::now()->subDays(2),
            ],
            [
                'nom' => 'Ndiaye',
                'prenom' => 'Fatou',
                'email' => 'fatou.ndiaye@example.com',
                'telephone' => '+221 77 234 56 78',
                'objet' => 'Demande d\'audience avec le DG',
                'description' => 'Je souhaite rencontrer le Directeur Général pour discuter d\'un partenariat.',
                'type_demande' => 'audience',
                'statut' => 'en_cours',
                'region' => 'Thiès',
                'commune' => 'Thiès Ville',
                'adresse' => 'Avenue Lamine Gueye, Thiès',
                'latitude' => 14.7889,
                'longitude' => -16.9278,
                'tracking_code' => 'CSAR-' . strtoupper(substr(md5(uniqid()), 0, 8)),
                'created_at' => Carbon::now()->subDays(5),
            ],
            [
                'nom' => 'Sow',
                'prenom' => 'Ibrahima',
                'email' => 'ibrahima.sow@example.com',
                'telephone' => '+221 77 345 67 89',
                'objet' => 'Information sur les programmes CSAR',
                'description' => 'Je voudrais obtenir des informations sur vos programmes d\'assistance.',
                'type_demande' => 'information',
                'statut' => 'traite',
                'region' => 'Saint-Louis',
                'commune' => 'Saint-Louis',
                'adresse' => 'Quartier Sud, Saint-Louis',
                'latitude' => 16.0183,
                'longitude' => -16.4897,
                'tracking_code' => 'CSAR-' . strtoupper(substr(md5(uniqid()), 0, 8)),
                'created_at' => Carbon::now()->subDays(7),
                'date_traitement' => Carbon::now()->subDays(1),
            ],
            [
                'nom' => 'Fall',
                'prenom' => 'Aissatou',
                'email' => 'aissatou.fall@example.com',
                'telephone' => '+221 77 456 78 90',
                'objet' => 'Demande d\'aide d\'urgence',
                'description' => 'Besoin urgent de soutien alimentaire pour 50 familles.',
                'type_demande' => 'aide_alimentaire',
                'statut' => 'en_attente',
                'region' => 'Kaolack',
                'commune' => 'Kaolack',
                'adresse' => 'Médina Baye, Kaolack',
                'latitude' => 14.1474,
                'longitude' => -16.0777,
                'tracking_code' => 'CSAR-' . strtoupper(substr(md5(uniqid()), 0, 8)),
                'created_at' => Carbon::now()->subHours(12),
            ],
            [
                'nom' => 'Sarr',
                'prenom' => 'Moussa',
                'email' => 'moussa.sarr@example.com',
                'telephone' => '+221 77 567 89 01',
                'objet' => 'Partenariat ONG locale',
                'description' => 'Notre ONG souhaite établir un partenariat avec CSAR.',
                'type_demande' => 'autre',
                'statut' => 'en_cours',
                'region' => 'Ziguinchor',
                'commune' => 'Ziguinchor',
                'adresse' => 'Centre-ville, Ziguinchor',
                'latitude' => 12.5492,
                'longitude' => -16.2650,
                'tracking_code' => 'CSAR-' . strtoupper(substr(md5(uniqid()), 0, 8)),
                'created_at' => Carbon::now()->subDays(3),
            ],
        ];
        
        foreach ($demandes as $demande) {
            Demande::create($demande);
        }
        
        $this->command->info('      ✅ ' . count($demandes) . ' demandes créées');
    }
    
    private function createPublicRequests($admin)
    {
        $this->command->info('   📝 Création des demandes publiques...');
        
        $requests = [
            [
                'full_name' => 'Khadija Ba',
                'email' => 'khadija.ba@example.com',
                'phone' => '+221 77 678 90 12',
                'subject' => 'Demande de stock alimentaire',
                'description' => 'Nous avons besoin de riz et d\'huile pour notre centre communautaire.',
                'type' => 'aide_alimentaire',
                'status' => 'pending',
                'region' => 'Louga',
                'address' => 'Quartier Escale, Louga',
                'latitude' => 15.6174,
                'longitude' => -16.2258,
                'tracking_code' => PublicRequest::generateTrackingCode(),
                'request_date' => Carbon::now()->subDays(1),
                'urgency' => 'high',
            ],
            [
                'full_name' => 'Omar Gueye',
                'email' => 'omar.gueye@example.com',
                'phone' => '+221 77 789 01 23',
                'subject' => 'Besoin d\'assistance d\'urgence',
                'description' => 'Suite aux inondations, nous avons besoin d\'aide alimentaire urgente.',
                'type' => 'aide_alimentaire',
                'status' => 'processing',
                'region' => 'Fatick',
                'address' => 'Fatick Centre',
                'latitude' => 14.3353,
                'longitude' => -16.4098,
                'tracking_code' => PublicRequest::generateTrackingCode(),
                'assigned_to' => $admin->id,
                'request_date' => Carbon::now()->subHours(6),
                'urgency' => 'urgent',
            ],
            [
                'full_name' => 'Mariama Touré',
                'email' => 'mariama.toure@example.com',
                'phone' => '+221 77 890 12 34',
                'subject' => 'Information sur les aides disponibles',
                'description' => 'Je voudrais savoir quels types d\'aide sont disponibles pour notre association.',
                'type' => 'information',
                'status' => 'completed',
                'region' => 'Kolda',
                'address' => 'Kolda',
                'latitude' => 12.9089,
                'longitude' => -14.9509,
                'tracking_code' => PublicRequest::generateTrackingCode(),
                'assigned_to' => $admin->id,
                'request_date' => Carbon::now()->subDays(4),
                'processed_date' => Carbon::now()->subDays(2),
                'admin_comment' => 'Demande traitée avec succès',
            ],
        ];
        
        foreach ($requests as $request) {
            PublicRequest::create($request);
        }
        
        $this->command->info('      ✅ ' . count($requests) . ' demandes publiques créées');
    }
    
    private function createNotifications($admin)
    {
        $this->command->info('   🔔 Création des notifications...');
        
        $notifications = [
            [
                'type' => 'new_request',
                'title' => 'Nouvelle demande reçue',
                'message' => 'Une nouvelle demande d\'aide alimentaire a été soumise par Khadija Ba',
                'icon' => 'bell',
                'read' => false,
                'user_id' => $admin->id,
                'created_at' => Carbon::now()->subHours(2),
            ],
            [
                'type' => 'low_stock',
                'title' => 'Stock faible détecté',
                'message' => 'Le stock de riz à l\'entrepôt de Dakar est en dessous du seuil minimum',
                'icon' => 'alert-triangle',
                'read' => false,
                'user_id' => $admin->id,
                'created_at' => Carbon::now()->subHours(5),
            ],
            [
                'type' => 'urgent_request',
                'title' => 'Demande urgente',
                'message' => 'Une demande urgente nécessite votre attention immédiate',
                'icon' => 'alert-circle',
                'read' => false,
                'user_id' => $admin->id,
                'created_at' => Carbon::now()->subMinutes(30),
            ],
            [
                'type' => 'stock_movement',
                'title' => 'Mouvement de stock',
                'message' => 'Un transfert de 500kg de maïs a été effectué vers l\'entrepôt de Thiès',
                'icon' => 'package',
                'read' => true,
                'user_id' => $admin->id,
                'read_at' => Carbon::now()->subHours(1),
                'created_at' => Carbon::now()->subHours(3),
            ],
            [
                'type' => 'request_processed',
                'title' => 'Demande traitée',
                'message' => 'La demande de Mariama Touré a été traitée avec succès',
                'icon' => 'check-circle',
                'read' => true,
                'user_id' => $admin->id,
                'read_at' => Carbon::now()->subDays(1),
                'created_at' => Carbon::now()->subDays(2),
            ],
        ];
        
        foreach ($notifications as $notification) {
            Notification::create($notification);
        }
        
        $this->command->info('      ✅ ' . count($notifications) . ' notifications créées');
    }
    
    private function createMessages($admin)
    {
        $this->command->info('   💬 Création des messages...');
        
        $messages = [
            [
                'sujet' => 'Question sur une demande d\'aide',
                'contenu' => 'Bonjour, j\'ai une question concernant la demande CSAR-12345. Pourriez-vous me donner plus d\'informations sur le statut de traitement ?',
                'expediteur' => 'Aminata Diallo',
                'email_expediteur' => 'aminata.diallo@example.com',
                'telephone_expediteur' => '+221 77 111 22 33',
                'lu' => false,
                'user_id' => $admin->id,
                'created_at' => Carbon::now()->subHours(4),
            ],
            [
                'sujet' => 'Demande d\'information sur les stocks',
                'contenu' => 'Bonjour, je voudrais savoir quels produits sont actuellement disponibles dans vos entrepôts. Merci.',
                'expediteur' => 'Ousmane Ba',
                'email_expediteur' => 'ousmane.ba@example.com',
                'telephone_expediteur' => '+221 77 222 33 44',
                'lu' => false,
                'user_id' => $admin->id,
                'created_at' => Carbon::now()->subHours(8),
            ],
            [
                'sujet' => 'Remerciements',
                'contenu' => 'Merci beaucoup pour l\'aide alimentaire reçue la semaine dernière. Cela a beaucoup aidé notre communauté.',
                'expediteur' => 'Fatou Sow',
                'email_expediteur' => 'fatou.sow@example.com',
                'telephone_expediteur' => '+221 77 333 44 55',
                'lu' => true,
                'lu_at' => Carbon::now()->subDays(1),
                'user_id' => $admin->id,
                'reponse' => 'Merci pour votre message. Nous sommes heureux d\'avoir pu vous aider !',
                'reponse_at' => Carbon::now()->subDays(1),
                'created_at' => Carbon::now()->subDays(2),
            ],
            [
                'sujet' => 'Partenariat ONG',
                'contenu' => 'Notre ONG souhaite établir un partenariat avec le CSAR. Pouvons-nous organiser une réunion ?',
                'expediteur' => 'Mamadou Cisse',
                'email_expediteur' => 'mamadou.cisse@ong-example.org',
                'telephone_expediteur' => '+221 77 444 55 66',
                'lu' => false,
                'user_id' => $admin->id,
                'created_at' => Carbon::now()->subMinutes(30),
            ],
        ];
        
        foreach ($messages as $message) {
            Message::create($message);
        }
        
        $this->command->info('      ✅ ' . count($messages) . ' messages créés');
    }
    
    private function createStockMovements($warehouses)
    {
        $this->command->info('   📦 Création des mouvements de stock...');
        
        $stocks = Stock::all();
        
        if ($stocks->isEmpty()) {
            $this->command->warn('      ⚠️  Aucun stock trouvé. Exécutez d\'abord StockSeeder.');
            return;
        }
        
        $admin = User::where('email', 'admin@csar.sn')->first();
        $count = 0;
        
        foreach ($stocks->take(3) as $stock) {
            $currentQty = $stock->quantity ?? 0;
            
            // Entrée de stock
            $entryQty = 100;
            StockMovement::create([
                'stock_id' => $stock->id,
                'warehouse_id' => $stock->warehouse_id,
                'type' => 'in',
                'quantity' => $entryQty,
                'quantity_before' => $currentQty,
                'quantity_after' => $currentQty + $entryQty,
                'reason' => 'Réapprovisionnement mensuel',
                'reference' => 'MVT-' . strtoupper(substr(md5(uniqid()), 0, 8)),
                'created_by' => $admin->id ?? 1,
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3),
            ]);
            $count++;
            
            // Sortie de stock
            $exitQty = 50;
            $newCurrentQty = $currentQty + $entryQty;
            StockMovement::create([
                'stock_id' => $stock->id,
                'warehouse_id' => $stock->warehouse_id,
                'type' => 'out',
                'quantity' => $exitQty,
                'quantity_before' => $newCurrentQty,
                'quantity_after' => $newCurrentQty - $exitQty,
                'reason' => 'Distribution d\'aide alimentaire',
                'reference' => 'MVT-' . strtoupper(substr(md5(uniqid()), 0, 8)),
                'created_by' => $admin->id ?? 1,
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1),
            ]);
            $count++;
        }
        
        $this->command->info('      ✅ ' . $count . ' mouvements de stock créés');
    }
}
