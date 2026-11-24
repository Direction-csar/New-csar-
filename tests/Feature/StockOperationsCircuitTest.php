<?php

namespace Tests\Feature;

use App\Models\StockMovement;
use App\Models\StockOperation;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Circuit opérationnel DG → DTL → Inspecteur → Magasinier.
 * Règle centrale : un mouvement 'en_attente' ne compte PAS dans le
 * stock officiel tant que l'inspecteur ne l'a pas vérifié.
 */
class StockOperationsCircuitTest extends TestCase
{
    protected User $dg;
    protected User $dtl;
    protected User $inspecteur;
    protected User $magasinier;
    protected Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSchema();

        $this->dg = $this->makeUser('dg');
        $this->dtl = $this->makeUser('dtl');
        $this->inspecteur = $this->makeUser('inspecteur');
        $this->magasinier = $this->makeUser('magasinier');

        $this->warehouse = Warehouse::create([
            'name' => 'Magasin Central', 'region' => 'Dakar',
            'capacity' => 1000, 'current_stock' => 0,
            'status' => 'active', 'is_active' => true,
        ]);
        $this->magasinier->warehouse_id = $this->warehouse->id;
        $this->magasinier->save();

        // Stock initial vérifié : 100 contenants de Riz [50kg]
        StockMovement::create([
            'warehouse_id' => $this->warehouse->id,
            'type' => 'entry', 'quantity' => 100,
            'reason' => 'Riz [50kg] — Réception initiale',
            'reference' => 'INIT-1', 'created_by' => $this->dg->id,
            'status' => 'verified',
        ]);
        $this->warehouse->update(['current_stock' => 100]);

        // Périmètre inspecteur : l'inspecteur couvre le magasin du test
        \App\Models\InspectorWarehouseAssignment::create([
            'inspector_id' => $this->inspecteur->id,
            'warehouse_id' => $this->warehouse->id,
            'assigned_by' => $this->dtl->id,
            'is_active' => true,
        ]);
    }

    private function makeUser(string $role): User
    {
        $user = User::create([
            'name' => 'Test ' . $role,
            'email' => $role . substr(md5(uniqid()), 0, 6) . '@csar.test',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $user->role = $role;
        $user->save();
        return $user->refresh();
    }

    private function createSchema(): void
    {
        foreach (['inspector_warehouse_assignments', 'stock_operations', 'stock_movements', 'warehouses', 'products', 'stocks', 'users', 'audit_logs', 'notifications', 'personal_access_tokens'] as $t) {
            Schema::dropIfExists($t);
        }

        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->timestamp('email_verified_at')->nullable();
            $t->string('password')->nullable();
            $t->string('role')->default('agent');
            $t->unsignedBigInteger('role_id')->nullable();
            $t->boolean('is_active')->default(true);
            $t->unsignedBigInteger('warehouse_id')->nullable();
            $t->timestamp('last_activity')->nullable();
            $t->rememberToken();
            $t->timestamps();
        });

        Schema::create('warehouses', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('address')->nullable();
            $t->string('region')->nullable();
            $t->string('city')->nullable();
            $t->decimal('latitude', 10, 7)->nullable();
            $t->decimal('longitude', 10, 7)->nullable();
            $t->decimal('capacity', 15, 2)->nullable();
            $t->decimal('current_stock', 15, 2)->nullable();
            $t->string('status')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('inspector_warehouse_assignments', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('inspector_id');
            $t->unsignedBigInteger('warehouse_id');
            $t->unsignedBigInteger('assigned_by')->nullable();
            $t->string('note')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamp('revoked_at')->nullable();
            $t->unsignedBigInteger('revoked_by')->nullable();
            $t->timestamps();
            $t->unique(['inspector_id', 'warehouse_id']);
        });

        Schema::create('stock_operations', function (Blueprint $t) {
            $t->id();
            $t->string('reference')->unique();
            $t->string('type');
            $t->unsignedBigInteger('warehouse_id');
            $t->unsignedBigInteger('destination_warehouse_id')->nullable();
            $t->string('produit')->nullable();
            $t->string('unite')->nullable();
            $t->unsignedSmallInteger('format')->nullable();
            $t->decimal('quantity_requested', 14, 2);
            $t->decimal('quantity_requested_kg', 14, 2)->nullable();
            $t->decimal('quantity_executed', 14, 2)->nullable();
            $t->decimal('quantity_executed_kg', 14, 2)->nullable();
            $t->text('instructions')->nullable();
            $t->text('dtl_comment')->nullable();
            $t->text('inspector_comment')->nullable();
            $t->text('anomaly_report')->nullable();
            $t->text('rejection_reason')->nullable();
            $t->json('attachments')->nullable();
            $t->string('status')->default('soumise');
            $t->unsignedBigInteger('created_by');
            $t->unsignedBigInteger('dtl_assigned_by')->nullable();
            $t->timestamp('dtl_assigned_at')->nullable();
            $t->unsignedBigInteger('assigned_inspector_id')->nullable();
            $t->timestamp('inspector_transmitted_at')->nullable();
            $t->unsignedBigInteger('assigned_magasinier_id')->nullable();
            $t->unsignedBigInteger('executed_by')->nullable();
            $t->timestamp('executed_at')->nullable();
            $t->unsignedBigInteger('verified_by')->nullable();
            $t->timestamp('verified_at')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('stock_movements', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('stock_id')->nullable();
            $t->unsignedBigInteger('warehouse_id');
            $t->string('type');
            $t->decimal('quantity', 15, 2)->default(0);
            $t->decimal('quantity_before', 15, 2)->nullable();
            $t->decimal('quantity_after', 15, 2)->nullable();
            $t->string('reason')->nullable();
            $t->string('reference')->nullable();
            $t->string('numero_transfert')->nullable();
            $t->unsignedBigInteger('entrepot_destination_id')->nullable();
            $t->string('raison_ajustement')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->text('metadata')->nullable();
            $t->unsignedBigInteger('public_request_id')->nullable();
            $t->unsignedBigInteger('request_mission_id')->nullable();
            $t->unsignedBigInteger('stock_operation_id')->nullable();
            $t->string('status')->default('draft');
            $t->unsignedBigInteger('validated_by')->nullable();
            $t->timestamp('validated_at')->nullable();
            $t->text('rejection_reason')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('category')->nullable();
            $t->string('unit')->nullable();
            $t->text('description')->nullable();
            $t->string('image')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('stocks', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('warehouse_id')->nullable();
            $t->string('item_name')->nullable();
            $t->decimal('quantity', 15, 2)->default(0);
            $t->date('expiry_date')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('action');
            $t->string('level')->nullable();
            $t->string('status')->nullable();
            $t->string('model_type')->nullable();
            $t->unsignedBigInteger('model_id')->nullable();
            $t->text('description')->nullable();
            $t->json('data')->nullable();
            $t->json('old_values')->nullable();
            $t->json('new_values')->nullable();
            $t->string('ip_address')->nullable();
            $t->string('user_agent')->nullable();
            $t->timestamps();
        });

        Schema::create('personal_access_tokens', function (Blueprint $t) {
            $t->id();
            $t->morphs('tokenable');
            $t->string('name');
            $t->string('token', 64)->unique();
            $t->text('abilities')->nullable();
            $t->timestamp('last_used_at')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->timestamps();
        });

        Schema::create('notifications', function (Blueprint $t) {
            $t->id();
            $t->string('title')->nullable();
            $t->text('message')->nullable();
            $t->string('type')->nullable();
            $t->string('icon')->nullable();
            $t->boolean('read')->default(false);
            $t->json('data')->nullable();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('notifiable_type')->nullable();
            $t->unsignedBigInteger('notifiable_id')->nullable();
            $t->string('action_url')->nullable();
            $t->timestamp('read_at')->nullable();
            $t->timestamps();
        });
    }

    private function createOperationViaService(): StockOperation
    {
        $svc = app(\App\Services\Magasin\StockOperationService::class);
        $r = $svc->create($this->dg, [
            'type' => 'exit',
            'warehouse_id' => $this->warehouse->id,
            'produit' => 'Riz', 'unite' => 'kg', 'format' => 50,
            'quantity_requested' => 10,
            'quantity_requested_kg' => 500,
            'instructions' => 'Sortie pour distribution régionale',
        ]);
        $this->assertTrue($r['ok'], $r['message'] ?? '');
        return $r['operation'];
    }

    public function test_full_circuit_g_until_verification(): void
    {
        $svc = app(\App\Services\Magasin\StockOperationService::class);
        $stock = app(\App\Services\Magasin\WarehouseStockService::class);

        // 1. DG crée l'instruction
        $op = $this->createOperationViaService();
        $this->assertSame('soumise', $op->status);

        // 2. DTL affecte l'inspecteur
        $r = $svc->assignInspector($this->dtl, $op->id, $this->inspecteur->id, 'Inspection demandée');
        $this->assertTrue($r['ok'], $r['message'] ?? '');
        $this->assertSame('affectee_inspecteur', $r['operation']->status);

        // 3. Inspecteur transmet au magasinier (rattaché au magasin)
        $r = $svc->transmitToMagasinier($this->inspecteur, $op->id, $this->magasinier->id, 'Contrôler le sac n° consignation');
        $this->assertTrue($r['ok'], $r['message'] ?? '');
        $this->assertSame('transmise_magasinier', $r['operation']->status);

        // Un autre magasinier ne peut pas exécuter
        $otherMag = $this->makeUser('magasinier');
        $otherMag->warehouse_id = $this->warehouse->id;
        $otherMag->save();
        $r = $svc->execute($otherMag, $op->id, ['quantity_executed' => 10]);
        $this->assertFalse($r['ok']);

        // 4. Magasinier exécute : mouvement 'en_attente' → stock officiel inchangé
        $r = $svc->execute($this->magasinier, $op->id, [
            'quantity_executed' => 10, 'quantity_executed_kg' => 500,
            'position' => 'Case A1', 'movement_date' => '2026-11-25',
        ]);
        $this->assertTrue($r['ok'], $r['message'] ?? '');
        $this->assertSame('executee', $r['operation']->status);

        $movement = StockMovement::where('stock_operation_id', $op->id)->first();
        $this->assertNotNull($movement);
        $this->assertSame('en_attente', $movement->status);
        $this->assertSame('exit', $movement->type);

        // Stock officiel : toujours 100 (le mouvement en attente ne compte pas)
        $this->assertEquals(100, $stock->balance($this->warehouse->id)['sacs']);
        $this->assertEquals(100, (float) $this->warehouse->fresh()->current_stock);

        // 5. Inspecteur vérifie → stock officiel = 90
        $r = $svc->verify($this->inspecteur, $op->id, 'Quantités conformes');
        $this->assertTrue($r['ok'], $r['message'] ?? '');
        $this->assertSame('verifiee', $r['operation']->status);
        $this->assertEquals(90, $stock->balance($this->warehouse->id)['sacs']);
        $this->assertEquals(90, (float) $this->warehouse->fresh()->current_stock);
        $this->assertSame('verifie', $movement->fresh()->status);
        $this->assertSame($this->inspecteur->id, $movement->fresh()->validated_by);
    }

    public function test_rejected_operation_never_touches_stock(): void
    {
        $svc = app(\App\Services\Magasin\StockOperationService::class);
        $stock = app(\App\Services\Magasin\WarehouseStockService::class);

        $op = $this->createOperationViaService();
        $svc->assignInspector($this->dtl, $op->id, $this->inspecteur->id);
        $svc->transmitToMagasinier($this->inspecteur, $op->id, $this->magasinier->id);
        $svc->execute($this->magasinier, $op->id, ['quantity_executed' => 10]);

        // L'inspecteur rejette
        $r = $svc->reject($this->inspecteur, $op->id, 'Justificatif non conforme');
        $this->assertTrue($r['ok'], $r['message'] ?? '');
        $this->assertSame('rejetee', $r['operation']->status);

        $this->assertEquals(100, $stock->balance($this->warehouse->id)['sacs']);
        $this->assertSame('rejetee', StockMovement::where('stock_operation_id', $op->id)->first()->status);

        // Impossible de re-vérifier une opération rejetée
        $r = $svc->verify($this->inspecteur, $op->id);
        $this->assertFalse($r['ok']);
    }

    public function test_anomaly_then_verify(): void
    {
        $svc = app(\App\Services\Magasin\StockOperationService::class);
        $stock = app(\App\Services\Magasin\WarehouseStockService::class);

        $op = $this->createOperationViaService();
        $svc->assignInspector($this->dtl, $op->id, $this->inspecteur->id);
        $svc->transmitToMagasinier($this->inspecteur, $op->id, $this->magasinier->id);
        $svc->execute($this->magasinier, $op->id, ['quantity_executed' => 10]);

        $r = $svc->reportAnomaly($this->inspecteur, $op->id, 'Écart de 2 sacs constaté', [['type' => 'photo']]);
        $this->assertTrue($r['ok']);
        $this->assertSame('anomalie', $r['operation']->status);
        // Toujours hors stock officiel
        $this->assertEquals(100, $stock->balance($this->warehouse->id)['sacs']);

        // Après régularisation, l'inspecteur peut vérifier
        $r = $svc->verify($this->inspecteur, $op->id, 'Écart régularisé');
        $this->assertTrue($r['ok']);
        $this->assertSame('verifiee', $r['operation']->status);
        $this->assertEquals(90, $stock->balance($this->warehouse->id)['sacs']);
    }

    public function test_transfer_operation_creates_two_pending_movements(): void
    {
        $svc = app(\App\Services\Magasin\StockOperationService::class);
        $stock = app(\App\Services\Magasin\WarehouseStockService::class);

        $dest = Warehouse::create([
            'name' => 'Magasin Thiès', 'region' => 'Thiès',
            'status' => 'active', 'is_active' => true, 'current_stock' => 0,
        ]);
        $mag2 = $this->makeUser('magasinier');
        $mag2->warehouse_id = $dest->id;
        $mag2->save();

        $r = $svc->create($this->dg, [
            'type' => 'transfer',
            'warehouse_id' => $this->warehouse->id,
            'destination_warehouse_id' => $dest->id,
            'produit' => 'Riz', 'unite' => 'kg', 'format' => 50,
            'quantity_requested' => 20,
        ]);
        $this->assertTrue($r['ok'], $r['message'] ?? '');
        $op = $r['operation'];

        // Le magasinier de destination est rattaché à la destination → OK pour un transfert
        $svc->assignInspector($this->dtl, $op->id, $this->inspecteur->id);
        $r = $svc->transmitToMagasinier($this->inspecteur, $op->id, $mag2->id);
        $this->assertTrue($r['ok'], $r['message'] ?? '');

        $r = $svc->execute($mag2, $op->id, ['quantity_executed' => 20]);
        $this->assertTrue($r['ok'], $r['message'] ?? '');

        $movements = StockMovement::where('stock_operation_id', $op->id)->get();
        $this->assertCount(2, $movements);
        $this->assertEqualsCanonicalizing(['en_attente', 'en_attente'], $movements->pluck('status')->all());

        // Officiel inchangé des deux côtés
        $this->assertEquals(100, $stock->balance($this->warehouse->id)['sacs']);
        $this->assertEquals(0, $stock->balance($dest->id)['sacs']);

        // La vérification est bloquée tant que le magasin destinataire
        // n'a pas confirmé la réception physique.
        $r = $svc->verify($this->inspecteur, $op->id);
        $this->assertFalse($r['ok']);
        $this->assertEquals(409, $r['status']);

        // Le magasinier de destination confirme la réception
        $inMovement = StockMovement::where('stock_operation_id', $op->id)
            ->where('warehouse_id', $dest->id)->first();
        $r = $stock->confirmTransferReceipt($mag2, $inMovement->id);
        $this->assertTrue($r['ok'], $r['message'] ?? '');

        $r = $svc->verify($this->inspecteur, $op->id);
        $this->assertTrue($r['ok'], $r['message'] ?? '');
        $this->assertEquals(80, $stock->balance($this->warehouse->id)['sacs']);
        $this->assertEquals(20, $stock->balance($dest->id)['sacs']);
    }

    public function test_double_execution_is_blocked(): void
    {
        $svc = app(\App\Services\Magasin\StockOperationService::class);

        $op = $this->createOperationViaService();
        $svc->assignInspector($this->dtl, $op->id, $this->inspecteur->id);
        $svc->transmitToMagasinier($this->inspecteur, $op->id, $this->magasinier->id);
        $svc->execute($this->magasinier, $op->id, ['quantity_executed' => 10]);

        // Seconde exécution refusée
        $r = $svc->execute($this->magasinier, $op->id, ['quantity_executed' => 10]);
        $this->assertFalse($r['ok']);
        $this->assertSame(1, StockMovement::where('stock_operation_id', $op->id)->count());
    }

    public function test_exit_beyond_official_balance_rejected_at_execution(): void
    {
        $svc = app(\App\Services\Magasin\StockOperationService::class);

        $r = $svc->create($this->dg, [
            'type' => 'exit', 'warehouse_id' => $this->warehouse->id,
            'quantity_requested' => 500,
        ]);
        $op = $r['operation'];
        $svc->assignInspector($this->dtl, $op->id, $this->inspecteur->id);
        $svc->transmitToMagasinier($this->inspecteur, $op->id, $this->magasinier->id);

        // Exécution de 500 alors que le stock officiel n'est que 100
        $r = $svc->execute($this->magasinier, $op->id, ['quantity_executed' => 500]);
        $this->assertFalse($r['ok']);
        $this->assertStringContainsString('insuffisant', mb_strtolower($r['message']));
    }

    // ------------------------------------------------------------------
    // API mobile (APK Inspecteur / APK Magasinier)
    // ------------------------------------------------------------------
    public function test_api_inspector_login_and_full_flow(): void
    {
        \Laravel\Sanctum\Sanctum::actingAs($this->inspecteur, ['inspector']);

        // L'inspecteur ne voit que ses opérations affectées
        $svc = app(\App\Services\Magasin\StockOperationService::class);
        $op = $this->createOperationViaService();
        $svc->assignInspector($this->dtl, $op->id, $this->inspecteur->id);

        $this->getJson('/api/warehouse/v1/operations')->assertOk()
            ->assertJsonCount(1, 'operations');
        $this->getJson("/api/warehouse/v1/operations/{$op->id}")->assertOk();

        // Transmission au magasinier via API
        $this->postJson("/api/warehouse/v1/operations/{$op->id}/transmit", [
            'assigned_magasinier_id' => $this->magasinier->id,
            'inspector_comment' => 'From APK',
        ])->assertOk()->assertJsonPath('operation.status', 'transmise_magasinier');
    }

    public function test_api_magasinier_execute_and_inspector_verify(): void
    {
        $svc = app(\App\Services\Magasin\StockOperationService::class);
        $op = $this->createOperationViaService();
        $svc->assignInspector($this->dtl, $op->id, $this->inspecteur->id);
        $svc->transmitToMagasinier($this->inspecteur, $op->id, $this->magasinier->id);

        // Magasinier exécute via API
        \Laravel\Sanctum\Sanctum::actingAs($this->magasinier, ['warehouse']);
        $this->getJson('/api/warehouse/v1/operations')->assertOk()
            ->assertJsonCount(1, 'operations');
        $this->postJson("/api/warehouse/v1/operations/{$op->id}/execute", [
            'quantity_executed' => 10, 'quantity_executed_kg' => 500,
        ])->assertOk()->assertJsonPath('operation.status', 'executee');

        // Un rôle non autorisé ne peut pas exécuter
        \Laravel\Sanctum\Sanctum::actingAs($this->inspecteur, ['inspector']);
        $this->postJson("/api/warehouse/v1/operations/{$op->id}/execute", [
            'quantity_executed' => 5,
        ])->assertStatus(403);

        // Inspecteur vérifie via API
        $this->postJson("/api/warehouse/v1/operations/{$op->id}/verify", [
            'inspector_comment' => 'OK terrain',
            'latitude' => 14.69, 'longitude' => -17.44,
        ])->assertOk()->assertJsonPath('operation.status', 'verifiee');

        $stock = app(\App\Services\Magasin\WarehouseStockService::class);
        $this->assertEquals(90, $stock->balance($this->warehouse->id)['sacs']);
    }

    public function test_api_inspector_login_endpoint(): void
    {
        // Inspecteur : login dédié OK
        $this->postJson('/api/warehouse/v1/login/inspecteur', [
            'email' => $this->inspecteur->email,
            'password' => 'password123',
        ])->assertOk()->assertJsonStructure(['token', 'user']);

        // Magasinier refusé sur l'endpoint inspecteur
        $this->postJson('/api/warehouse/v1/login/inspecteur', [
            'email' => $this->magasinier->email,
            'password' => 'password123',
        ])->assertStatus(403);

        // Inspecteur refusé sur l'endpoint magasinier
        $this->postJson('/api/warehouse/v1/login', [
            'email' => $this->inspecteur->email,
            'password' => 'password123',
        ])->assertStatus(403);
    }

    public function test_api_magasinier_candidates_for_transmit(): void
    {
        \Laravel\Sanctum\Sanctum::actingAs($this->inspecteur, ['inspector']);

        // Sans warehouse_id → 422
        $this->getJson('/api/warehouse/v1/operations/magasinier-candidates')
            ->assertStatus(422);

        // Avec le magasin de l'opération → le magasinier rattaché est proposé
        $this->getJson('/api/warehouse/v1/operations/magasinier-candidates?warehouse_id=' . $this->warehouse->id)
            ->assertOk()
            ->assertJsonCount(1, 'magasiniers')
            ->assertJsonPath('magasiniers.0.id', $this->magasinier->id);

        // Un magasin hors du périmètre inspecteur est refusé
        $other = Warehouse::create([
            'name' => 'Autre', 'region' => 'Kaolack',
            'capacity' => 10, 'current_stock' => 0,
            'status' => 'active', 'is_active' => true,
        ]);
        $this->getJson('/api/warehouse/v1/operations/magasinier-candidates?warehouse_id=' . $other->id)
            ->assertForbidden();
    }

    public function test_audit_trail_is_written(): void
    {
        $svc = app(\App\Services\Magasin\StockOperationService::class);

        $op = $this->createOperationViaService();
        $svc->assignInspector($this->dtl, $op->id, $this->inspecteur->id);
        $svc->transmitToMagasinier($this->inspecteur, $op->id, $this->magasinier->id);
        $svc->execute($this->magasinier, $op->id, ['quantity_executed' => 10]);
        $svc->verify($this->inspecteur, $op->id);

        $actions = \App\Models\AuditLog::where('model_id', $op->id)
            ->where('model_type', StockOperation::class)->pluck('action')->all();

        $this->assertContains('stock_operation.create', $actions);
        $this->assertContains('stock_operation.assign_inspector', $actions);
        $this->assertContains('stock_operation.transmit', $actions);
        $this->assertContains('stock_operation.execute', $actions);
        $this->assertContains('stock_operation.verify', $actions);
    }

    // ------------------------------------------------------------------
    // Mouvements directs (hors stock_operations) : aucun ne doit modifier
    // le stock officiel sans vérification — Web, APK, API ou /sync.
    // ------------------------------------------------------------------

    private function movementData(string $type, float $sacs, array $extra = []): array
    {
        return $extra + [
            'warehouse_id' => $this->warehouse->id,
            'movement_type' => $type,
            'sacs' => $sacs,
            'kg' => $sacs * 50,
            'label' => 'Riz [50kg] — test',
            'movement_date' => '2026-11-25',
        ];
    }

    public function test_direct_entry_stays_pending_until_inspector_verifies(): void
    {
        $stock = app(\App\Services\Magasin\WarehouseStockService::class);

        $r = $stock->recordMovement($this->magasinier, $this->movementData('entry', 10, ['client_ref' => 'DIR-1']));
        $this->assertTrue($r['ok'], $r['message'] ?? '');

        $m = StockMovement::where('reference', 'DIR-1')->first();
        $this->assertSame('en_attente', $m->status);
        $this->assertSame($this->magasinier->id, $m->created_by);

        // Stock officiel et current_stock inchangés
        $this->assertEquals(100, $stock->balance($this->warehouse->id)['sacs']);
        $this->assertEquals(100, (float) $this->warehouse->fresh()->current_stock);

        // Le magasinier ne peut pas vérifier son propre mouvement
        $r = $stock->verifyPendingMovement($this->magasinier, $m->id);
        $this->assertFalse($r['ok']);
        $this->assertEquals(403, $r['status']);

        // L'inspecteur vérifie → intégré au stock officiel
        $r = $stock->verifyPendingMovement($this->inspecteur, $m->id);
        $this->assertTrue($r['ok'], $r['message'] ?? '');
        $this->assertEquals(110, $stock->balance($this->warehouse->id)['sacs']);
        $this->assertEquals(110, (float) $this->warehouse->fresh()->current_stock);
        $this->assertSame('verifie', $m->fresh()->status);
        $this->assertSame($this->inspecteur->id, $m->fresh()->validated_by);
        $this->assertNotNull($m->fresh()->validated_at);
    }

    public function test_direct_exit_deducted_only_after_verification(): void
    {
        $stock = app(\App\Services\Magasin\WarehouseStockService::class);

        $r = $stock->recordMovement($this->magasinier, $this->movementData('exit', 30, ['client_ref' => 'DIR-OUT-1']));
        $this->assertTrue($r['ok'], $r['message'] ?? '');

        // Officiel toujours 100 tant que non vérifié
        $this->assertEquals(100, $stock->balance($this->warehouse->id)['sacs']);
        $this->assertEquals(100, (float) $this->warehouse->fresh()->current_stock);

        $stock->verifyPendingMovement($this->inspecteur, StockMovement::where('reference', 'DIR-OUT-1')->first()->id);
        $this->assertEquals(70, $stock->balance($this->warehouse->id)['sacs']);
        $this->assertEquals(70, (float) $this->warehouse->fresh()->current_stock);
    }

    public function test_pending_exits_are_rechecked_against_official_balance(): void
    {
        $stock = app(\App\Services\Magasin\WarehouseStockService::class);

        // Deux sorties de 60 en attente sur un stock officiel de 100 :
        // les deux sont acceptées à la saisie (solde officiel = 100)…
        $stock->recordMovement($this->magasinier, $this->movementData('exit', 60, ['client_ref' => 'P-OUT-1']));
        $stock->recordMovement($this->magasinier, $this->movementData('exit', 60, ['client_ref' => 'P-OUT-2']));

        // …mais la seconde est refusée à la vérification (solde officiel = 40)
        $m1 = StockMovement::where('reference', 'P-OUT-1')->first();
        $m2 = StockMovement::where('reference', 'P-OUT-2')->first();
        $this->assertTrue($stock->verifyPendingMovement($this->inspecteur, $m1->id)['ok']);
        $this->assertEquals(40, $stock->balance($this->warehouse->id)['sacs']);

        $r = $stock->verifyPendingMovement($this->inspecteur, $m2->id);
        $this->assertFalse($r['ok']);
        $this->assertEquals(422, $r['status']);
        $this->assertEquals(40, $stock->balance($this->warehouse->id)['sacs']);
        $this->assertSame('en_attente', $m2->fresh()->status);
    }

    public function test_rejected_direct_movement_never_counts(): void
    {
        $stock = app(\App\Services\Magasin\WarehouseStockService::class);

        $stock->recordMovement($this->magasinier, $this->movementData('entry', 50, ['client_ref' => 'DIR-REJ']));
        $m = StockMovement::where('reference', 'DIR-REJ')->first();

        $r = $stock->rejectPendingMovement($this->inspecteur, $m->id, 'Justificatif absent');
        $this->assertTrue($r['ok'], $r['message'] ?? '');
        $this->assertSame('rejetee', $m->fresh()->status);
        $this->assertSame('Justificatif absent', $m->fresh()->rejection_reason);

        $this->assertEquals(100, $stock->balance($this->warehouse->id)['sacs']);
        $this->assertEquals(100, (float) $this->warehouse->fresh()->current_stock);

        // Un mouvement rejeté ne peut plus être vérifié
        $r = $stock->verifyPendingMovement($this->inspecteur, $m->id);
        $this->assertFalse($r['ok']);
    }

    public function test_transfer_requires_origin_check_and_destination_receipt(): void
    {
        $stock = app(\App\Services\Magasin\WarehouseStockService::class);
        $dest = Warehouse::create([
            'name' => 'Magasin Kaolack', 'region' => 'Kaolack',
            'capacity' => 500, 'current_stock' => 0,
            'status' => 'active', 'is_active' => true,
        ]);
        $magDest = $this->makeUser('magasinier');
        $magDest->warehouse_id = $dest->id;
        $magDest->save();

        // L'inspecteur couvre aussi le magasin de destination pour ce transfert
        \App\Models\InspectorWarehouseAssignment::create([
            'inspector_id' => $this->inspecteur->id,
            'warehouse_id' => $dest->id,
            'assigned_by' => $this->dtl->id,
            'is_active' => true,
        ]);

        // Le DG enregistre le transfert (accès à tous les magasins)
        $r = $stock->recordTransfer($this->dg, [
            'warehouse_id' => $this->warehouse->id,
            'destination_warehouse_id' => $dest->id,
            'sacs' => 20, 'kg' => 1000,
            'label' => 'Riz [50kg] — transfert',
            'movement_date' => '2026-11-25',
            'client_ref' => 'TRF-DIR-1',
        ]);
        $this->assertTrue($r['ok'], $r['message'] ?? '');

        $out = StockMovement::where('reference', 'TRF-DIR-1')->first();
        $in = StockMovement::where('reference', 'TRF-DIR-1-IN')->first();
        $this->assertSame('en_attente', $out->status);
        $this->assertSame('en_attente', $in->status);

        // Aucun stock officiel ne bouge
        $this->assertEquals(100, $stock->balance($this->warehouse->id)['sacs']);
        $this->assertEquals(0, $stock->balance($dest->id)['sacs']);

        // La réception ne peut pas être vérifiée avant confirmation du destinataire
        $r = $stock->verifyPendingMovement($this->inspecteur, $in->id);
        $this->assertFalse($r['ok']);
        $this->assertEquals(409, $r['status']);

        // Le magasinier destinataire confirme la réception physique
        $r = $stock->confirmTransferReceipt($magDest, $in->id);
        $this->assertTrue($r['ok'], $r['message'] ?? '');
        $meta = json_decode($in->fresh()->metadata, true);
        $this->assertSame($magDest->id, $meta['receipt_confirmed_by']);
        $this->assertNotEmpty($meta['receipt_confirmed_at']);

        // Idempotent : une seconde confirmation ne duplique rien
        $r = $stock->confirmTransferReceipt($magDest, $in->id);
        $this->assertTrue($r['ok']);
        $this->assertTrue($r['duplicate'] ?? false);

        // Sortie vérifiée par l'inspecteur, réception vérifiée ensuite
        $this->assertTrue($stock->verifyPendingMovement($this->inspecteur, $out->id)['ok']);
        $this->assertTrue($stock->verifyPendingMovement($this->inspecteur, $in->id)['ok']);

        $this->assertEquals(80, $stock->balance($this->warehouse->id)['sacs']);
        $this->assertEquals(20, $stock->balance($dest->id)['sacs']);
    }

    public function test_inventory_correction_requires_dtl_or_dg(): void
    {
        $stock = app(\App\Services\Magasin\WarehouseStockService::class);

        $r = $stock->recordInventory($this->magasinier, [
            'warehouse_id' => $this->warehouse->id,
            'theoretical_sacs' => 100,
            'actual_sacs' => 92,
            'label' => 'Inventaire annuel',
            'movement_date' => '2026-11-25',
        ]);
        $this->assertTrue($r['ok'], $r['message'] ?? '');
        $this->assertEquals(-8, $r['difference_sacs']);

        $m = StockMovement::where('reference', $r['reference'])->first();
        $this->assertSame('en_attente', $m->status);
        $this->assertEquals(100, $stock->balance($this->warehouse->id)['sacs']);

        // L'inspecteur ne peut pas valider un ajustement d'inventaire
        $r = $stock->verifyPendingMovement($this->inspecteur, $m->id);
        $this->assertFalse($r['ok']);
        $this->assertEquals(403, $r['status']);

        // La DTL valide → correction appliquée (100 → 92)
        $r = $stock->verifyPendingMovement($this->dtl, $m->id);
        $this->assertTrue($r['ok'], $r['message'] ?? '');
        $this->assertEquals(92, $stock->balance($this->warehouse->id)['sacs']);
        $this->assertEquals(92, (float) $this->warehouse->fresh()->current_stock);
    }

    public function test_offline_sync_movements_stay_pending_and_idempotent(): void
    {
        $stock = app(\App\Services\Magasin\WarehouseStockService::class);

        \Laravel\Sanctum\Sanctum::actingAs($this->magasinier, ['warehouse']);
        $payload = ['movements' => [[
            'warehouse_id' => $this->warehouse->id,
            'movement_date' => '2026-11-25',
            'label' => 'Riz [50kg] — offline',
            'movement_type' => 'entry',
            'sacs' => 15, 'kg' => 750,
            'client_ref' => 'OFFLINE-1',
        ]]];

        $this->postJson('/api/warehouse/v1/sync', $payload)->assertOk();
        // Re-synchronisation (retry réseau) : aucun doublon
        $r2 = $this->postJson('/api/warehouse/v1/sync', $payload)->assertOk();
        $this->assertSame('duplicate', $r2->json('results.0.status'));

        $this->assertSame(1, StockMovement::where('reference', 'OFFLINE-1')->count());
        $this->assertSame('en_attente', StockMovement::where('reference', 'OFFLINE-1')->first()->status);
        $this->assertEquals(100, $stock->balance($this->warehouse->id)['sacs']);
        $this->assertEquals(100, (float) $this->warehouse->fresh()->current_stock);
    }

    public function test_api_pending_movements_endpoints(): void
    {
        $stock = app(\App\Services\Magasin\WarehouseStockService::class);
        $stock->recordMovement($this->magasinier, $this->movementData('entry', 5, ['client_ref' => 'API-PEND-1']));
        $m = StockMovement::where('reference', 'API-PEND-1')->first();

        // L'inspecteur voit le mouvement en attente et le vérifie via l'API
        \Laravel\Sanctum\Sanctum::actingAs($this->inspecteur, ['inspector']);
        $this->getJson('/api/warehouse/v1/pending-movements')->assertOk()
            ->assertJsonCount(1, 'movements')
            ->assertJsonPath('movements.0.reference', 'API-PEND-1');

        $this->postJson("/api/warehouse/v1/pending-movements/{$m->id}/verify")
            ->assertOk()->assertJsonPath('movement.status', 'verifie');
        $this->assertEquals(105, $stock->balance($this->warehouse->id)['sacs']);

        // Le magasinier voit ses propres mouvements en attente (lecture seule)
        $stock->recordMovement($this->magasinier, $this->movementData('exit', 5, ['client_ref' => 'API-PEND-2']));
        $m2 = StockMovement::where('reference', 'API-PEND-2')->first();
        \Laravel\Sanctum\Sanctum::actingAs($this->magasinier, ['warehouse']);
        $this->getJson('/api/warehouse/v1/pending-movements')->assertOk()
            ->assertJsonCount(1, 'movements');
        // …mais ne peut ni vérifier ni rejeter
        $this->postJson("/api/warehouse/v1/pending-movements/{$m2->id}/verify")->assertStatus(403);
        $this->postJson("/api/warehouse/v1/pending-movements/{$m2->id}/reject", [
            'rejection_reason' => 'test',
        ])->assertStatus(403);
        $this->assertEquals(105, $stock->balance($this->warehouse->id)['sacs']);
    }

    public function test_client_ref_idempotency_on_direct_movements(): void
    {
        $stock = app(\App\Services\Magasin\WarehouseStockService::class);

        $r1 = $stock->recordMovement($this->magasinier, $this->movementData('entry', 7, ['client_ref' => 'IDEM-1']));
        $r2 = $stock->recordMovement($this->magasinier, $this->movementData('entry', 7, ['client_ref' => 'IDEM-1']));

        $this->assertTrue($r1['ok']);
        $this->assertTrue($r2['ok']);
        $this->assertTrue($r2['duplicate']);
        $this->assertSame(1, StockMovement::where('reference', 'IDEM-1')->count());
        $this->assertEquals(100, $stock->balance($this->warehouse->id)['sacs']);
    }
}
