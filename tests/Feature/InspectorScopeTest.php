<?php

namespace Tests\Feature;

use App\Models\InspectorWarehouseAssignment;
use App\Models\StockMovement;
use App\Models\StockOperation;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\Magasin\StockOperationService;
use App\Services\Magasin\WarehouseStockService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Périmètre inspecteur : un inspecteur ne doit voir/agir que sur les
 * magasins qui lui sont affectés (inspector_warehouse_assignments),
 * sur le Web comme sur l'API — même en manipulant les paramètres.
 */
class InspectorScopeTest extends TestCase
{
    protected User $dtl;
    protected User $inspecteur;
    protected User $inspecteur2;
    protected User $magasinier;
    protected Warehouse $whA;
    protected Warehouse $whB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSchema();

        $this->dtl = $this->makeUser('dtl');
        $this->inspecteur = $this->makeUser('inspecteur');
        $this->inspecteur2 = $this->makeUser('inspecteur');
        $this->magasinier = $this->makeUser('magasinier');

        $this->whA = $this->makeWarehouse('Magasin A');
        $this->whB = $this->makeWarehouse('Magasin B');
        $this->magasinier->warehouse_id = $this->whA->id;
        $this->magasinier->save();

        // Stock initial vérifié sur les deux magasins
        foreach ([$this->whA, $this->whB] as $w) {
            StockMovement::create([
                'warehouse_id' => $w->id, 'type' => 'entry', 'quantity' => 100,
                'reason' => 'Riz [50kg] — Réception initiale',
                'reference' => 'INIT-' . $w->id, 'created_by' => $this->dtl->id,
                'status' => 'verified',
            ]);
            $w->update(['current_stock' => 100]);
        }

        // L'inspecteur 1 est affecté au magasin A uniquement
        InspectorWarehouseAssignment::create([
            'inspector_id' => $this->inspecteur->id,
            'warehouse_id' => $this->whA->id,
            'assigned_by' => $this->dtl->id,
            'is_active' => true,
        ]);
        // L'inspecteur 2 est affecté aux deux magasins
        foreach ([$this->whA, $this->whB] as $w) {
            InspectorWarehouseAssignment::create([
                'inspector_id' => $this->inspecteur2->id,
                'warehouse_id' => $w->id,
                'assigned_by' => $this->dtl->id,
                'is_active' => true,
            ]);
        }
    }

    private function makeUser(string $role): User
    {
        $user = User::create([
            'name' => 'Test ' . $role . ' ' . substr(md5(uniqid()), 0, 4),
            'email' => $role . substr(md5(uniqid()), 0, 8) . '@csar.test',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $user->role = $role;
        $user->save();
        return $user->refresh();
    }

    private function makeWarehouse(string $name): Warehouse
    {
        return Warehouse::create([
            'name' => $name, 'region' => 'Dakar',
            'capacity' => 1000, 'current_stock' => 0,
            'status' => 'active', 'is_active' => true,
        ]);
    }

    private function pendingMovement(int $warehouseId, string $ref, string $type = 'entry'): StockMovement
    {
        return StockMovement::create([
            'warehouse_id' => $warehouseId, 'type' => $type, 'quantity' => 10,
            'quantity_before' => 100, 'quantity_after' => 110,
            'reason' => 'Riz [50kg] — Test', 'reference' => $ref,
            'created_by' => $this->magasinier->id, 'status' => 'en_attente',
        ]);
    }

    // ---------------------------------------------------------------
    // pendingMovements : filtrage par périmètre
    // ---------------------------------------------------------------
    public function test_inspecteur_ne_voit_que_les_mouvements_de_son_perimetre(): void
    {
        $this->pendingMovement($this->whA->id, 'MVT-A-1');
        $this->pendingMovement($this->whB->id, 'MVT-B-1');

        $stock = app(WarehouseStockService::class);
        $rows = $stock->pendingMovements($this->inspecteur);

        $this->assertCount(1, $rows);
        $this->assertSame('MVT-A-1', $rows->first()->reference);
    }

    public function test_inspecteur_sans_affectation_ne_voit_rien(): void
    {
        $orphelin = $this->makeUser('inspecteur');
        $this->pendingMovement($this->whA->id, 'MVT-A-2');

        $stock = app(WarehouseStockService::class);
        $this->assertCount(0, $stock->pendingMovements($orphelin));
    }

    // ---------------------------------------------------------------
    // verify / reject : blocage hors périmètre même avec l'ID en main
    // ---------------------------------------------------------------
    public function test_inspecteur_ne_peut_pas_verifier_mouvement_hors_perimetre(): void
    {
        $m = $this->pendingMovement($this->whB->id, 'MVT-B-2');
        $stock = app(WarehouseStockService::class);

        $r = $stock->verifyPendingMovement($this->inspecteur, $m->id);
        $this->assertFalse($r['ok']);
        $this->assertSame(403, $r['status']);
        $this->assertSame('en_attente', $m->fresh()->status);
        $this->assertSame(100.0, (float) $this->whB->fresh()->current_stock);
    }

    public function test_inspecteur_ne_peut_pas_rejeter_mouvement_hors_perimetre(): void
    {
        $m = $this->pendingMovement($this->whB->id, 'MVT-B-3');
        $stock = app(WarehouseStockService::class);

        $r = $stock->rejectPendingMovement($this->inspecteur, $m->id, 'hors périmètre');
        $this->assertFalse($r['ok']);
        $this->assertSame(403, $r['status']);
    }

    public function test_inspecteur_verifie_dans_son_perimetre(): void
    {
        $m = $this->pendingMovement($this->whA->id, 'MVT-A-3');
        $stock = app(WarehouseStockService::class);

        $r = $stock->verifyPendingMovement($this->inspecteur, $m->id);
        $this->assertTrue($r['ok']);
        $this->assertSame(110.0, (float) $this->whA->fresh()->current_stock);
    }

    // ---------------------------------------------------------------
    // API : stock-status, warehouses, pending-movements, verify
    // ---------------------------------------------------------------
    private function token(User $u): string
    {
        return $u->createToken('test')->plainTextToken;
    }

    public function test_api_stock_status_refuse_magasin_hors_perimetre(): void
    {
        $res = $this->withToken($this->token($this->inspecteur))
            ->getJson('/api/warehouse/v1/stock-ops/status?warehouse_id=' . $this->whB->id);
        $res->assertStatus(403);
    }

    public function test_api_stock_status_ok_dans_perimetre(): void
    {
        $res = $this->withToken($this->token($this->inspecteur))
            ->getJson('/api/warehouse/v1/stock-ops/status?warehouse_id=' . $this->whA->id);
        $res->assertOk();
    }

    public function test_api_warehouses_ne_renvoie_que_le_perimetre(): void
    {
        $res = $this->withToken($this->token($this->inspecteur))
            ->getJson('/api/warehouse/v1/stock-ops/warehouses');
        $res->assertOk();
        $ids = collect($res->json('warehouses'))->pluck('id')->all();
        $this->assertContains($this->whA->id, $ids);
        $this->assertNotContains($this->whB->id, $ids);
    }

    public function test_api_warehouses_inspecteur_liste_ses_magasiniers(): void
    {
        $res = $this->withToken($this->token($this->inspecteur))
            ->getJson('/api/warehouse/v1/stock-ops/warehouses');
        $res->assertOk();
        $wh = collect($res->json('warehouses'))->firstWhere('id', $this->whA->id);
        $this->assertSame([$this->magasinier->id], collect($wh['magasiniers'])->pluck('id')->all());
    }

    public function test_couverture_14_regions_signale_les_manques(): void
    {
        $svc = app(\App\Services\Magasin\InspectorCoverageService::class);
        $report = collect($svc->report())->keyBy('region');

        $this->assertCount(14, collect(\App\Services\Magasin\InspectorCoverageService::REGIONS));
        $this->assertSame('Dakar', $this->whA->region);
        $dakar = $report['Dakar'];
        $this->assertCount(2, $dakar['warehouses']);
        $this->assertStringContainsString('sans magasinier', $dakar['status']); // magasin B sans magasinier
        $this->assertSame('AUCUN MAGASIN', $report['Kolda']['status']);
    }

    public function test_provision_inspecteurs_simulation_ne_cree_rien_puis_apply_idempotent(): void
    {
        $csv = tempnam(sys_get_temp_dir(), 'insp');
        file_put_contents($csv, "nom;email;telephone;regions\nInspecteur Reel;reel@csar.test;770000000;Dakar|Kolda\nBad;pas-un-email;;Dakar\n");

        $this->artisan('inspectors:provision', ['file' => $csv])->assertSuccessful();
        $this->assertNull(User::where('email', 'reel@csar.test')->first());

        $this->artisan('inspectors:provision', ['file' => $csv, '--apply' => true])->assertFailed(); // ligne invalide signalée
        $u = User::where('email', 'reel@csar.test')->first();
        $this->assertNotNull($u);
        $this->assertSame('inspecteur', $u->role);
        $this->assertSame(2, InspectorWarehouseAssignment::where('inspector_id', $u->id)->where('is_active', true)->count());

        $this->artisan('inspectors:provision', ['file' => $csv, '--apply' => true]);
        $this->assertSame(1, User::where('email', 'reel@csar.test')->count());
        $this->assertSame(2, InspectorWarehouseAssignment::where('inspector_id', $u->id)->count());
        foreach (glob(storage_path('app/private/inspecteurs-identifiants-*.csv')) as $f) { @unlink($f); }
        @unlink($csv);
    }

    public function test_api_pending_movements_filtrees_par_perimetre(): void
    {
        $this->pendingMovement($this->whA->id, 'MVT-A-4');
        $this->pendingMovement($this->whB->id, 'MVT-B-4');

        $res = $this->withToken($this->token($this->inspecteur))
            ->getJson('/api/warehouse/v1/pending-movements');
        $res->assertOk();
        $refs = collect($res->json('movements'))->pluck('reference')->all();
        $this->assertContains('MVT-A-4', $refs);
        $this->assertNotContains('MVT-B-4', $refs);
    }

    public function test_api_verify_mouvement_hors_perimetre_refuse(): void
    {
        $m = $this->pendingMovement($this->whB->id, 'MVT-B-5');
        $res = $this->withToken($this->token($this->inspecteur))
            ->postJson("/api/warehouse/v1/pending-movements/{$m->id}/verify");
        $res->assertStatus(403);
    }

    public function test_api_magasinier_candidates_hors_perimetre_refuse(): void
    {
        $res = $this->withToken($this->token($this->inspecteur))
            ->getJson('/api/warehouse/v1/operations/magasinier-candidates?warehouse_id=' . $this->whB->id);
        $res->assertStatus(403);
    }

    // ---------------------------------------------------------------
    // Opérations stock : affectation, consultation, vérification
    // ---------------------------------------------------------------
    private function makeOperation(int $warehouseId, ?int $destId = null): StockOperation
    {
        return StockOperation::create([
            'reference' => 'OP-' . strtoupper(substr(md5(uniqid()), 0, 8)),
            'type' => $destId ? 'transfer' : 'exit',
            'warehouse_id' => $warehouseId,
            'destination_warehouse_id' => $destId,
            'produit' => 'Riz', 'unite' => 'kg', 'format' => 50,
            'quantity_requested' => 5,
            'status' => StockOperation::STATUS_SOUMISE,
            'created_by' => $this->dtl->id,
        ]);
    }

    public function test_dtl_ne_peut_pas_affecter_inspecteur_hors_perimetre(): void
    {
        $op = $this->makeOperation($this->whB->id); // magasin B : inspecteur1 non affecté
        $ops = app(StockOperationService::class);

        $r = $ops->assignInspector($this->dtl, $op->id, $this->inspecteur->id);
        $this->assertFalse($r['ok']);
        $this->assertSame(422, $r['status']);
    }

    public function test_dtl_peut_affecter_inspecteur_du_perimetre(): void
    {
        $op = $this->makeOperation($this->whA->id);
        $ops = app(StockOperationService::class);

        $r = $ops->assignInspector($this->dtl, $op->id, $this->inspecteur->id);
        $this->assertTrue($r['ok']);
    }

    public function test_inspecteur_ne_consulte_pas_operation_hors_perimetre(): void
    {
        // Opération affectée à l'inspecteur2 (qui couvre B), tentée par inspecteur1
        $op = $this->makeOperation($this->whB->id);
        $ops = app(StockOperationService::class);
        $ops->assignInspector($this->dtl, $op->id, $this->inspecteur2->id);
        $ops->transmitToMagasinier($this->inspecteur2, $op->id, $this->magasinier->id);

        // inspecteur1 tente de transmettre → non affecté (assigned_inspector_id)
        $r = $ops->transmitToMagasinier($this->inspecteur, $op->id, $this->magasinier->id);
        $this->assertFalse($r['ok']);

        // inspecteur1 tente de rejeter → non autorisé
        $r = $ops->reject($this->inspecteur, $op->id, 'test');
        $this->assertFalse($r['ok']);
    }

    public function test_inspecteur_perd_acces_apres_revocation(): void
    {
        $stock = app(WarehouseStockService::class);

        // Avant révocation : accès OK
        $this->assertTrue($stock->inspectorCanAccessWarehouse($this->inspecteur, $this->whA->id));

        InspectorWarehouseAssignment::where('inspector_id', $this->inspecteur->id)
            ->where('warehouse_id', $this->whA->id)->update([
                'is_active' => false, 'revoked_at' => now(), 'revoked_by' => $this->dtl->id,
            ]);
        // Recharger les relations
        $this->inspecteur = $this->inspecteur->fresh();

        $this->assertFalse($stock->inspectorCanAccessWarehouse($this->inspecteur, $this->whA->id));
        $this->assertCount(0, $stock->pendingMovements($this->inspecteur));
    }

    // ---------------------------------------------------------------
    // Schéma minimal (même convention que StockOperationsCircuitTest)
    // ---------------------------------------------------------------
    private function createSchema(): void
    {
        foreach (['inspector_warehouse_assignments', 'stock_operations', 'stock_movements', 'warehouses', 'products', 'stocks', 'users', 'audit_logs', 'notifications', 'personal_access_tokens'] as $t) {
            Schema::dropIfExists($t);
        }

        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('email')->unique();
            $t->timestamp('email_verified_at')->nullable(); $t->string('password')->nullable();
            $t->string('phone')->nullable(); $t->string('role')->default('agent'); $t->unsignedBigInteger('role_id')->nullable();
            $t->boolean('is_active')->default(true); $t->unsignedBigInteger('warehouse_id')->nullable();
            $t->timestamp('last_activity')->nullable(); $t->rememberToken(); $t->timestamps();
        });

        Schema::create('warehouses', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('address')->nullable(); $t->string('region')->nullable();
            $t->string('city')->nullable(); $t->decimal('latitude', 10, 7)->nullable();
            $t->decimal('longitude', 10, 7)->nullable(); $t->decimal('capacity', 15, 2)->nullable();
            $t->decimal('current_stock', 15, 2)->nullable(); $t->string('status')->nullable();
            $t->boolean('is_active')->default(true); $t->timestamps();
        });

        Schema::create('inspector_warehouse_assignments', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('inspector_id'); $t->unsignedBigInteger('warehouse_id');
            $t->unsignedBigInteger('assigned_by')->nullable(); $t->string('note')->nullable();
            $t->boolean('is_active')->default(true); $t->timestamp('revoked_at')->nullable();
            $t->unsignedBigInteger('revoked_by')->nullable(); $t->timestamps();
            $t->unique(['inspector_id', 'warehouse_id']);
        });

        Schema::create('stock_operations', function (Blueprint $t) {
            $t->id(); $t->string('reference')->unique(); $t->string('type');
            $t->unsignedBigInteger('warehouse_id'); $t->unsignedBigInteger('destination_warehouse_id')->nullable();
            $t->string('produit')->nullable(); $t->string('unite')->nullable(); $t->unsignedSmallInteger('format')->nullable();
            $t->decimal('quantity_requested', 14, 2); $t->decimal('quantity_requested_kg', 14, 2)->nullable();
            $t->decimal('quantity_executed', 14, 2)->nullable(); $t->decimal('quantity_executed_kg', 14, 2)->nullable();
            $t->text('instructions')->nullable(); $t->text('dtl_comment')->nullable(); $t->text('inspector_comment')->nullable();
            $t->text('anomaly_report')->nullable(); $t->text('rejection_reason')->nullable();
            $t->json('attachments')->nullable(); $t->string('status')->default('soumise');
            $t->unsignedBigInteger('created_by'); $t->unsignedBigInteger('dtl_assigned_by')->nullable();
            $t->timestamp('dtl_assigned_at')->nullable(); $t->unsignedBigInteger('assigned_inspector_id')->nullable();
            $t->timestamp('inspector_transmitted_at')->nullable(); $t->unsignedBigInteger('assigned_magasinier_id')->nullable();
            $t->unsignedBigInteger('executed_by')->nullable(); $t->timestamp('executed_at')->nullable();
            $t->unsignedBigInteger('verified_by')->nullable(); $t->timestamp('verified_at')->nullable();
            $t->timestamps(); $t->softDeletes();
        });

        Schema::create('stock_movements', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('stock_id')->nullable(); $t->unsignedBigInteger('warehouse_id');
            $t->string('type'); $t->decimal('quantity', 15, 2)->default(0);
            $t->decimal('quantity_before', 15, 2)->nullable(); $t->decimal('quantity_after', 15, 2)->nullable();
            $t->string('reason')->nullable(); $t->string('reference')->nullable(); $t->string('numero_transfert')->nullable();
            $t->unsignedBigInteger('entrepot_destination_id')->nullable(); $t->string('raison_ajustement')->nullable();
            $t->unsignedBigInteger('created_by')->nullable(); $t->text('metadata')->nullable();
            $t->unsignedBigInteger('public_request_id')->nullable(); $t->unsignedBigInteger('request_mission_id')->nullable();
            $t->unsignedBigInteger('stock_operation_id')->nullable(); $t->string('status')->default('draft');
            $t->unsignedBigInteger('validated_by')->nullable(); $t->timestamp('validated_at')->nullable();
            $t->text('rejection_reason')->nullable(); $t->timestamps(); $t->softDeletes();
        });

        Schema::create('products', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('category')->nullable(); $t->string('unit')->nullable();
            $t->text('description')->nullable(); $t->string('image')->nullable();
            $t->boolean('is_active')->default(true); $t->timestamps();
        });

        Schema::create('stocks', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('warehouse_id')->nullable(); $t->string('item_name')->nullable();
            $t->decimal('quantity', 15, 2)->default(0); $t->date('expiry_date')->nullable();
            $t->boolean('is_active')->default(true); $t->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id')->nullable(); $t->string('action');
            $t->string('level')->nullable(); $t->string('status')->nullable();
            $t->string('model_type')->nullable(); $t->unsignedBigInteger('model_id')->nullable();
            $t->text('description')->nullable(); $t->json('data')->nullable();
            $t->json('old_values')->nullable(); $t->json('new_values')->nullable();
            $t->string('ip_address')->nullable(); $t->string('user_agent')->nullable(); $t->timestamps();
        });

        Schema::create('personal_access_tokens', function (Blueprint $t) {
            $t->id(); $t->morphs('tokenable'); $t->string('name'); $t->string('token', 64)->unique();
            $t->text('abilities')->nullable(); $t->timestamp('last_used_at')->nullable();
            $t->timestamp('expires_at')->nullable(); $t->timestamps();
        });

        Schema::create('notifications', function (Blueprint $t) {
            $t->id(); $t->string('title')->nullable(); $t->text('message')->nullable();
            $t->string('type')->nullable(); $t->string('icon')->nullable();
            $t->boolean('read')->default(false); $t->json('data')->nullable();
            $t->unsignedBigInteger('user_id')->nullable(); $t->string('notifiable_type')->nullable();
            $t->unsignedBigInteger('notifiable_id')->nullable(); $t->string('action_url')->nullable();
            $t->timestamp('read_at')->nullable(); $t->timestamps();
        });
    }
}
