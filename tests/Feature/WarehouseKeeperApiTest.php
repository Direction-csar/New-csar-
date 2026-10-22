<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Laravel\Sanctum\Sanctum;

/**
 * Tests de l'API mobile magasinier (/api/warehouse/v1).
 *
 * Le projet contient des migrations dupliquées (ex. warehouses créée deux
 * fois) : RefreshDatabase n'est donc pas utilisable. Le schéma minimal est
 * créé à la main sur SQLite :memory: — jamais la production.
 */
class WarehouseKeeperApiTest extends TestCase
{
    protected User $admin;
    protected User $magasinier;
    protected Warehouse $whA;
    protected Warehouse $whB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSchema();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->whA = Warehouse::create([
            'name' => 'Magasin Test A',
            'capacity' => 5000,
            'current_stock' => 0,
            'status' => 'active',
            'is_active' => true,
        ]);
        $this->whB = Warehouse::create([
            'name' => 'Magasin Test B',
            'capacity' => 5000,
            'current_stock' => 0,
            'status' => 'active',
            'is_active' => true,
        ]);

        // Magasinier rattaché au magasin A (relation users.warehouse_id)
        $this->magasinier = User::factory()->create([
            'role' => 'magasinier',
            'is_active' => true,
            'warehouse_id' => $this->whA->id,
        ]);

        Product::create([
            'name' => 'Riz Test',
            'category' => 'Céréales',
            'unit' => 'kg',
            'description' => json_encode(['formats_kg' => [5, 25, 50]]),
            'is_active' => 1,
        ]);
        Product::create([
            'name' => 'Huile Test',
            'category' => 'Huiles',
            'unit' => 'L',
            'description' => json_encode(['formats_l' => [5, 10, 20]]),
            'is_active' => 1,
        ]);
    }

    /**
     * Schéma minimal : uniquement les tables nécessaires aux tests.
     * Recréé à chaque test pour isoler les données.
     */
    private function createSchema(): void
    {
        foreach (['request_missions', 'public_requests', 'stock_movements', 'stocks', 'products', 'warehouses', 'users'] as $t) {
            Schema::dropIfExists($t);
        }

        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->timestamp('email_verified_at')->nullable();
            $t->string('password');
            $t->string('role')->default('admin');
            $t->unsignedBigInteger('warehouse_id')->nullable();
            $t->boolean('is_active')->default(true);
            $t->rememberToken();
            $t->timestamps();
        });

        Schema::create('warehouses', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('address')->nullable();
            $t->string('region')->nullable();
            $t->float('capacity')->default(0);
            $t->float('current_stock')->default(0);
            $t->string('status')->default('active');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('category')->nullable();
            $t->string('unit')->default('kg');
            $t->text('description')->nullable();
            $t->string('image')->nullable();
            $t->decimal('unit_price', 10, 2)->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('stocks', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('warehouse_id');
            $t->string('item_name')->nullable();
            $t->float('quantity')->default(0);
            $t->float('min_quantity')->default(0);
            $t->date('expiry_date')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('stock_movements', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('warehouse_id');
            $t->unsignedBigInteger('stock_id')->nullable();
            $t->string('type');
            $t->decimal('quantity', 10, 2);
            $t->decimal('quantity_before', 10, 2)->default(0);
            $t->decimal('quantity_after', 10, 2)->default(0);
            $t->string('reason')->nullable();
            $t->string('reference')->unique();
            $t->integer('reference_number')->default(0);
            $t->unsignedBigInteger('created_by')->nullable();
            $t->text('metadata')->nullable();
            $t->string('numero_transfert')->nullable();
            $t->unsignedBigInteger('entrepot_destination_id')->nullable();
            $t->string('raison_ajustement')->nullable();
            $t->unsignedBigInteger('public_request_id')->nullable();
            $t->unsignedBigInteger('request_mission_id')->nullable();
            $t->timestamps();
        });

        Schema::create('public_requests', function (Blueprint $t) {
            $t->id();
            $t->string('tracking_code')->unique();
            $t->string('full_name')->nullable();
            $t->string('region')->nullable();
            $t->string('type')->nullable();
            $t->string('status')->default('pending');
            $t->string('workflow_status')->default('soumise');
            $t->text('workflow_history')->nullable();
            $t->timestamps();
        });

        Schema::create('request_missions', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('public_request_id');
            $t->string('type'); // inspection | stock_exit
            $t->unsignedBigInteger('assigned_by')->nullable();
            $t->unsignedBigInteger('assigned_to')->nullable();
            $t->text('instructions')->nullable();
            $t->string('status')->default('affectee');
            $t->text('report')->nullable();
            $t->text('attachments')->nullable();
            $t->decimal('visit_latitude', 10, 7)->nullable();
            $t->decimal('visit_longitude', 10, 7)->nullable();
            $t->timestamp('visited_at')->nullable();
            $t->timestamp('reported_at')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
        });
    }

    private function makeMissionFor(User $assignee, string $status = 'affectee'): \App\Models\RequestMission
    {
        $request = \App\Models\PublicRequest::create([
            'tracking_code' => 'CSAR-TEST-' . uniqid(),
            'full_name' => 'Demandeur Test',
            'region' => 'Dakar',
            'type' => 'aide_alimentaire',
            'status' => 'approved',
            'workflow_status' => 'approuvee',
        ]);

        return \App\Models\RequestMission::create([
            'public_request_id' => $request->id,
            'type' => \App\Models\RequestMission::TYPE_STOCK_EXIT,
            'assigned_by' => $this->admin->id,
            'assigned_to' => $assignee->id,
            'instructions' => 'Livrer les kits au point de distribution.',
            'status' => $status,
        ]);
    }

    private function apiAdmin()
    {
        Sanctum::actingAs($this->admin, ['warehouse']);
        return $this;
    }

    private function apiMagasinier()
    {
        Sanctum::actingAs($this->magasinier, ['warehouse']);
        return $this;
    }

    private function postEntry(int $wh, string $label, float $sacs, float $kg, ?string $ref = null)
    {
        return $this->postJson('/api/warehouse/v1/movements', [
            'warehouse_id' => $wh,
            'movement_date' => '2026-10-20',
            'label' => $label,
            'movement_type' => 'entry',
            'sacs' => $sacs,
            'kg' => $kg,
            'client_ref' => $ref,
        ]);
    }

    /** 16. GET /missions ne retourne que les missions stock_exit du magasinier */
    public function test_get_missions_returns_only_assigned_stock_exit()
    {
        $this->apiMagasinier();

        $own = $this->makeMissionFor($this->magasinier);
        $this->makeMissionFor($this->admin); // mission d'un autre utilisateur

        $r = $this->getJson('/api/warehouse/v1/missions')->assertStatus(200);

        $missions = $r->json('missions');
        $this->assertCount(1, $missions);
        $this->assertSame($own->id, $missions[0]['id']);
        $this->assertSame('affectee', $missions[0]['status']);
        $this->assertNotNull($missions[0]['tracking_code']);
    }

    /** 17. POST /missions/{id}/report clôture la mission et lie le mouvement */
    public function test_report_mission_closes_and_links_movement()
    {
        // Créer un mouvement de sortie dans le magasin A (magasin du magasinier)
        $this->apiAdmin();
        $this->postEntry($this->whA->id, 'Riz Test [25kg]', 10, 250, 'T-IN-M1')->assertStatus(201);
        $exit = $this->postJson('/api/warehouse/v1/movements', [
            'warehouse_id' => $this->whA->id,
            'movement_date' => '2026-10-20',
            'label' => 'Riz Test [25kg]',
            'movement_type' => 'exit',
            'sacs' => 2,
            'kg' => 50,
            'client_ref' => 'T-OUT-M1',
        ])->assertStatus(201);
        $ref = $exit->json('reference');

        $mission = $this->makeMissionFor($this->magasinier);

        $this->apiMagasinier();
        $this->postJson("/api/warehouse/v1/missions/{$mission->id}/report", [
            'report' => 'Sortie effectuée et remise au convoi.',
            'movement_reference' => $ref,
        ])->assertStatus(200);

        $mission->refresh();
        $this->assertSame('terminee', $mission->status);
        $this->assertNotNull($mission->completed_at);

        $movement = StockMovement::where('reference', $ref)->first();
        $this->assertSame($mission->id, $movement->request_mission_id);
        $this->assertSame($mission->public_request_id, $movement->public_request_id);
    }

    /** 18. report refuse un justificatif provenant d'un magasin non autorisé */
    public function test_report_mission_rejects_movement_from_other_warehouse()
    {
        $this->apiAdmin();
        $this->postEntry($this->whB->id, 'Riz Test [25kg]', 10, 250, 'T-IN-M2')->assertStatus(201);
        $exit = $this->postJson('/api/warehouse/v1/movements', [
            'warehouse_id' => $this->whB->id,
            'movement_date' => '2026-10-20',
            'label' => 'Riz Test [25kg]',
            'movement_type' => 'exit',
            'sacs' => 1,
            'kg' => 25,
            'client_ref' => 'T-OUT-M2',
        ])->assertStatus(201);
        $ref = $exit->json('reference');

        $mission = $this->makeMissionFor($this->magasinier);

        // Le magasinier n'a accès qu'au magasin A → refus 403
        $this->apiMagasinier();
        $this->postJson("/api/warehouse/v1/missions/{$mission->id}/report", [
            'report' => 'Tentative de rattacher une sortie du magasin B.',
            'movement_reference' => $ref,
        ])->assertStatus(403);

        $this->assertSame('affectee', $mission->fresh()->status);
    }

    /** 19. report refuse une référence qui n'est pas une sortie de stock */
    public function test_report_mission_rejects_entry_movement_reference()
    {
        $this->apiAdmin();
        $entry = $this->postEntry($this->whA->id, 'Riz Test [25kg]', 10, 250, 'T-IN-M3');
        $entry->assertStatus(201);
        $ref = $entry->json('reference');

        $mission = $this->makeMissionFor($this->magasinier);

        $this->apiMagasinier();
        $this->postJson("/api/warehouse/v1/missions/{$mission->id}/report", [
            'report' => 'Tentative de rattacher une entrée comme justificatif.',
            'movement_reference' => $ref,
        ])->assertStatus(422);

        $this->assertSame('affectee', $mission->fresh()->status);
    }

    /** 1. Entrée de 40 sacs de riz 25kg = +1000 kg */
    public function test_entry_40_sacs_riz_25kg()
    {
        $this->apiAdmin();
        $r = $this->postEntry($this->whA->id, 'Riz Test [25kg]', 40, 1000, 'T-ENTRY-1');
        $r->assertStatus(201);

        $status = $this->getJson('/api/warehouse/v1/stock-status?warehouse_id=' . $this->whA->id)
            ->assertStatus(200)->json('products');

        $this->assertNotEmpty($status);
        $this->assertSame(40.0, (float) $status[0]['total_sacs']);
        $this->assertSame(1000.0, (float) $status[0]['total_kg']);
        $this->assertSame('kg', $status[0]['unit']);
    }

    /** 2. Sortie de 5 sacs 25kg = -125 kg */
    public function test_exit_5_sacs_riz_25kg()
    {
        $this->apiAdmin();
        $this->postEntry($this->whA->id, 'Riz Test [25kg]', 40, 1000, 'T-IN');
        $this->postJson('/api/warehouse/v1/movements', [
            'warehouse_id' => $this->whA->id,
            'movement_date' => '2026-10-20',
            'label' => 'Riz Test [25kg]',
            'movement_type' => 'exit',
            'sacs' => 5,
            'kg' => 125,
        ])->assertStatus(201);

        $status = $this->getJson('/api/warehouse/v1/stock-status?warehouse_id=' . $this->whA->id)
            ->json('products');
        $this->assertSame(35.0, (float) $status[0]['total_sacs']);
        $this->assertSame(875.0, (float) $status[0]['total_kg']);
    }

    /** 3. Huile : 15 bidons de 20L = 300 L, unité L (jamais kg) */
    public function test_huile_en_litres()
    {
        $this->apiAdmin();
        $this->postEntry($this->whA->id, 'Huile Test [20L]', 15, 300, 'T-HUILE')->assertStatus(201);

        $status = $this->getJson('/api/warehouse/v1/stock-status?warehouse_id=' . $this->whA->id)
            ->json('products');
        $this->assertSame('L', $status[0]['unit']);
        $this->assertSame(15.0, (float) $status[0]['total_sacs']);
        $this->assertSame(300.0, (float) $status[0]['total_kg']); // valeur = 300 L
    }

    /** L'API produits renvoie unité et formats litres pour l'huile */
    public function test_products_api_renvoie_unite_et_formats()
    {
        $this->apiAdmin();
        $products = $this->getJson('/api/warehouse/v1/products')->assertStatus(200)->json('products');

        $huile = collect($products)->firstWhere('name', 'Huile Test');
        $this->assertSame('L', $huile['unit']);
        $this->assertSame([5, 10, 20], $huile['formats_kg']);

        $riz = collect($products)->firstWhere('name', 'Riz Test');
        $this->assertSame('kg', $riz['unit']);
        $this->assertSame([5, 25, 50], $riz['formats_kg']);
    }

    /** 4. Sortie refusée si stock insuffisant */
    public function test_sortie_refusee_stock_insuffisant()
    {
        $this->apiAdmin();
        $this->postEntry($this->whA->id, 'Riz Test [25kg]', 10, 250, 'T-IN2');

        $this->postJson('/api/warehouse/v1/movements', [
            'warehouse_id' => $this->whA->id,
            'movement_date' => '2026-10-20',
            'label' => 'Riz Test [25kg]',
            'movement_type' => 'exit',
            'sacs' => 50,
            'kg' => 1250,
        ])->assertStatus(422);

        // Aucun mouvement de sortie créé
        $this->assertSame(0, StockMovement::where('type', 'exit')->count());
    }

    /** 5. Un mouvement web (in/out) participe au calcul mobile */
    public function test_mouvement_web_compte_dans_stock_mobile()
    {
        $this->apiAdmin();
        $stock = Stock::create([
            'warehouse_id' => $this->whA->id,
            'item_name' => 'Riz Web',
            'quantity' => 100,
            'is_active' => true,
        ]);
        StockMovement::create([
            'warehouse_id' => $this->whA->id,
            'stock_id' => $stock->id,
            'type' => 'in',
            'quantity' => 100,
            'quantity_before' => 0,
            'quantity_after' => 100,
            'reason' => 'Réappro web',
            'reference' => 'WEB-IN-1',
            'created_by' => $this->admin->id,
        ]);
        StockMovement::create([
            'warehouse_id' => $this->whA->id,
            'stock_id' => $stock->id,
            'type' => 'out',
            'quantity' => 30,
            'quantity_before' => 100,
            'quantity_after' => 70,
            'reason' => 'Distribution web',
            'reference' => 'WEB-OUT-1',
            'created_by' => $this->admin->id,
        ]);

        // La fiche de stock doit compter +100 et -30 → 70 contenants
        $sheet = $this->getJson('/api/warehouse/v1/warehouses/' . $this->whA->id . '/sheet')
            ->assertStatus(200);
        $this->assertSame(70.0, (float) $sheet->json('balance_sacs'));

        // Le statut par produit voit le lot "Riz Web" via stock_id
        $status = $this->getJson('/api/warehouse/v1/stock-status?warehouse_id=' . $this->whA->id)
            ->json('products');
        $rizWeb = collect($status)->firstWhere('product_name', 'Riz Web');
        $this->assertNotNull($rizWeb);
        $this->assertSame(70.0, (float) $rizWeb['total_sacs']);
    }

    /** 6. Mouvement mobile visible avec les bons totaux (web lit la même table) */
    public function test_mouvement_mobile_visible_cote_web()
    {
        $this->apiAdmin();
        $this->postEntry($this->whA->id, 'Riz Test [25kg]', 10, 250, 'T-MOB-1')->assertStatus(201);

        $movement = StockMovement::where('reference', 'T-MOB-1')->first();
        $this->assertNotNull($movement);
        $this->assertSame('entry', $movement->type);
        $this->assertSame(10.0, (float) $movement->quantity);
        $meta = json_decode($movement->metadata, true);
        $this->assertSame(250.0, (float) $meta['entry_kg']);
    }

    /** 7. Transfert entre deux magasins */
    public function test_transfert_entre_magasins()
    {
        $this->apiAdmin();
        $this->postEntry($this->whA->id, 'Riz Test [25kg]', 20, 500, 'T-SRC');

        $r = $this->postJson('/api/warehouse/v1/transfers', [
            'warehouse_id' => $this->whA->id,
            'destination_warehouse_id' => $this->whB->id,
            'movement_date' => '2026-10-20',
            'label' => 'Riz Test [25kg]',
            'sacs' => 5,
            'kg' => 125,
            'client_ref' => 'TRF-TEST-1',
        ])->assertStatus(201);

        // Source : 20 - 5 = 15 ; destination : 5
        $sA = $this->getJson('/api/warehouse/v1/stock-status?warehouse_id=' . $this->whA->id)->json('products');
        $sB = $this->getJson('/api/warehouse/v1/stock-status?warehouse_id=' . $this->whB->id)->json('products');
        $this->assertSame(15.0, (float) $sA[0]['total_sacs']);
        $this->assertSame(5.0, (float) $sB[0]['total_sacs']);
        $this->assertNotNull($r->json('numero_transfert'));
    }

    /** 8. Transfert impossible : aucune écriture partielle */
    public function test_transfert_stock_insuffisant_aucune_ecriture()
    {
        $this->apiAdmin();
        $this->postEntry($this->whA->id, 'Riz Test [25kg]', 5, 125, 'T-SRC2');

        $this->postJson('/api/warehouse/v1/transfers', [
            'warehouse_id' => $this->whA->id,
            'destination_warehouse_id' => $this->whB->id,
            'movement_date' => '2026-10-20',
            'label' => 'Riz Test [25kg]',
            'sacs' => 50,
            'kg' => 1250,
        ])->assertStatus(422);

        $this->assertSame(0, StockMovement::where('type', 'transfer')->count());
        // Aucune écriture partielle côté destination
        $this->assertSame(0, StockMovement::where('warehouse_id', $this->whB->id)->count());
    }

    /** 9. Synchronisation répétée : pas de doublon (client_ref) */
    public function test_sync_idempotente_sans_doublon()
    {
        $this->apiAdmin();
        $payload = [
            'movements' => [[
                'warehouse_id' => $this->whA->id,
                'movement_date' => '2026-10-20',
                'label' => 'Riz Test [25kg]',
                'movement_type' => 'entry',
                'sacs' => 10,
                'kg' => 250,
                'client_ref' => 'SYNC-REF-1',
            ]],
        ];

        $this->postJson('/api/warehouse/v1/sync', $payload)->assertStatus(200);
        $r2 = $this->postJson('/api/warehouse/v1/sync', $payload)->assertStatus(200);

        $this->assertSame('duplicate', $r2->json('results.0.status'));
        $this->assertSame(1, StockMovement::where('reference', 'SYNC-REF-1')->count());
    }

    /** 10. Sortie concurrente : le solde reste cohérent après 2 sorties séquentielles */
    public function test_sorties_sequentielles_coherentes()
    {
        $this->apiAdmin();
        $this->postEntry($this->whA->id, 'Riz Test [25kg]', 10, 250, 'T-IN3');

        $this->postJson('/api/warehouse/v1/movements', [
            'warehouse_id' => $this->whA->id, 'movement_date' => '2026-10-20',
            'label' => 'Riz Test [25kg]', 'movement_type' => 'exit', 'sacs' => 6, 'kg' => 150,
        ])->assertStatus(201);

        // La seconde sortie voit le nouveau solde (4) → refusée
        $this->postJson('/api/warehouse/v1/movements', [
            'warehouse_id' => $this->whA->id, 'movement_date' => '2026-10-20',
            'label' => 'Riz Test [25kg]', 'movement_type' => 'exit', 'sacs' => 6, 'kg' => 150,
        ])->assertStatus(422);
    }

    /** 11. Inventaire avec écart positif et négatif */
    public function test_inventaire_ecart_positif_et_negatif()
    {
        $this->apiAdmin();
        $this->postEntry($this->whA->id, 'Riz Test [25kg]', 20, 500, 'T-INV');

        // Écart négatif : compté 18 au lieu de 20
        $this->postJson('/api/warehouse/v1/inventory', [
            'warehouse_id' => $this->whA->id,
            'movement_date' => '2026-10-20',
            'label' => 'Inventaire test',
            'theoretical_sacs' => 20,
            'actual_sacs' => 18,
        ])->assertStatus(201)->assertJson(['difference_sacs' => -2]);

        // Écart positif : compté 25
        $this->postJson('/api/warehouse/v1/inventory', [
            'warehouse_id' => $this->whA->id,
            'movement_date' => '2026-10-20',
            'label' => 'Inventaire test 2',
            'theoretical_sacs' => 18,
            'actual_sacs' => 25,
        ])->assertStatus(201)->assertJson(['difference_sacs' => 7]);

        $this->assertSame(2, StockMovement::where('type', 'adjustment')->count());
    }

    /** 12. Magasinier non rattaché → 403 */
    public function test_magasinier_magasin_non_attribue_refuse()
    {
        $this->apiMagasinier(); // rattaché à whA seulement

        // whB ne lui appartient pas
        $this->postEntry($this->whB->id, 'Riz Test [25kg]', 10, 250)->assertStatus(403);
        $this->getJson('/api/warehouse/v1/stock-status?warehouse_id=' . $this->whB->id)
            ->assertStatus(403);

        // whA lui appartient
        $this->postEntry($this->whA->id, 'Riz Test [25kg]', 10, 250, 'T-OK')->assertStatus(201);
    }

    /** 14. Reçu cohérent après mouvement */
    public function test_receipt_coherent()
    {
        $this->apiAdmin();
        $this->postEntry($this->whA->id, 'Riz Test [25kg]', 10, 250, 'T-REC');

        $receipt = $this->getJson('/api/warehouse/v1/receipt/T-REC')->assertStatus(200)->json('receipt');
        $this->assertSame('T-REC', $receipt['reference']);
        $this->assertSame('Entrée de stock', $receipt['type_label']);
        $this->assertSame(10.0, (float) $receipt['quantity_sacs']);
        $this->assertSame(10.0, (float) $receipt['quantity_after']);
    }

    /** 15. Ancien mouvement sans client_ref ni unité : agrégation inchangée */
    public function test_compatibilite_anciens_mouvements()
    {
        $this->apiAdmin();
        StockMovement::create([
            'warehouse_id' => $this->whA->id,
            'type' => 'entry',
            'quantity' => 8,
            'quantity_before' => 0,
            'quantity_after' => 8,
            'reason' => 'Riz ancien [50kg]',
            'reference' => 'OLD-1',
            'created_by' => $this->admin->id,
        ]);

        $status = $this->getJson('/api/warehouse/v1/stock-status?warehouse_id=' . $this->whA->id)
            ->json('products');
        $this->assertSame(8.0, (float) $status[0]['total_sacs']);
        $this->assertSame(400.0, (float) $status[0]['total_kg']); // 8 × 50kg
    }
}
