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
        foreach (['stock_operations', 'stock_movements', 'warehouses', 'products', 'stocks', 'users', 'audit_logs', 'notifications'] as $t) {
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

        $svc->verify($this->inspecteur, $op->id);
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
}
