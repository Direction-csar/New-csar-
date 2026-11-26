<?php

namespace Tests\Feature;

use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Portail web « Magasinier » (/magasinier) : login direction, tableau
 * de bord, fiche de stock, création de mouvement — mêmes règles que
 * l'API mobile WarehouseKeeper via WarehouseStockService.
 */
class MagasinierPortalTest extends TestCase
{
    protected User $magasinier;
    protected User $comptable;
    protected Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSchema();

        $this->magasinier = $this->makeUser('magasinier');
        $this->comptable = $this->makeUser('comptable');
        $this->warehouse = Warehouse::create([
            'name' => 'Magasin Test',
            'region' => 'Dakar',
            'capacity' => 1000,
            'current_stock' => 100,
            'status' => 'active',
            'is_active' => true,
        ]);
        // Le magasinier est rattaché au magasin via users.warehouse_id
        $this->magasinier->warehouse_id = $this->warehouse->id;
        $this->magasinier->save();
    }

    private function makeUser(string $role): User
    {
        $user = User::create([
            'name' => 'Web ' . $role,
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
        foreach (['stock_movements', 'warehouses', 'products', 'stocks', 'users'] as $t) {
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
            $t->decimal('capacity', 15, 2)->nullable();
            $t->decimal('current_stock', 15, 2)->nullable();
            $t->string('status')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
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
            $t->string('status')->default('draft');
            $t->unsignedBigInteger('validated_by')->nullable();
            $t->timestamp('validated_at')->nullable();
            $t->text('rejection_reason')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('stock_operations', function (Blueprint $t) {
            $t->id();
            $t->string('reference')->nullable();
            $t->string('type');
            $t->unsignedBigInteger('warehouse_id');
            $t->unsignedBigInteger('destination_warehouse_id')->nullable();
            $t->string('produit')->nullable();
            $t->string('unite')->nullable();
            $t->decimal('quantity_requested', 14, 2)->nullable();
            $t->decimal('quantity_executed', 14, 2)->nullable();
            $t->string('status')->default('soumise');
            $t->unsignedBigInteger('created_by')->nullable();
            $t->unsignedBigInteger('assigned_inspector_id')->nullable();
            $t->unsignedBigInteger('assigned_magasinier_id')->nullable();
            $t->unsignedBigInteger('executed_by')->nullable();
            $t->timestamp('executed_at')->nullable();
            $t->unsignedBigInteger('verified_by')->nullable();
            $t->timestamp('verified_at')->nullable();
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

        Schema::create('request_missions', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('public_request_id')->nullable();
            $t->string('type')->nullable();
            $t->unsignedBigInteger('assigned_by')->nullable();
            $t->unsignedBigInteger('assigned_to')->nullable();
            $t->text('instructions')->nullable();
            $t->string('status')->nullable();
            $t->text('report')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
        });

        Schema::create('public_requests', function (Blueprint $t) {
            $t->id();
            $t->string('tracking_code')->nullable();
            $t->string('full_name')->nullable();
            $t->string('region')->nullable();
            $t->string('workflow_status')->nullable();
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
    }

    public function test_login_page_and_magasinier_login(): void
    {
        $this->get(route('magasinier.login'))->assertOk();

        $this->post(route('magasinier.login.submit'), [
            'email' => $this->magasinier->email,
            'password' => 'password123',
        ])->assertRedirect(route('magasinier.dashboard'));

        $this->get(route('magasinier.dashboard'))->assertOk();
    }

    public function test_comptable_cannot_login_on_magasinier_portal(): void
    {
        $this->post(route('magasinier.login.submit'), [
            'email' => $this->comptable->email,
            'password' => 'password123',
        ])->assertSessionHasErrors();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('magasinier.dashboard'))->assertRedirect(route('magasinier.login'));
    }

    public function test_stock_sheet_and_movement_creation(): void
    {
        $this->actingAs($this->magasinier, 'magasinier');
        \App\Models\Product::create(['name' => 'Riz brisé', 'unit' => 'kg', 'category' => 'Céréales', 'is_active' => 1]);
        \App\Models\Product::create(['name' => 'Huile', 'unit' => 'Litres', 'category' => 'Huiles', 'is_active' => 1]);

        $this->get(route('magasinier.warehouses.show', $this->warehouse->id))->assertOk();
        $this->get(route('magasinier.mouvements.create', $this->warehouse->id))
            ->assertOk()->assertSee('Huile')->assertSee('Format du contenant');

        $this->post(route('magasinier.mouvements.store', $this->warehouse->id), [
            'movement_date' => '2026-11-14', 'label' => 'Réception huile', 'product' => 'Huile',
            'format' => 20, 'movement_type' => 'entry', 'sacs' => 10, 'kg' => 200,
        ])->assertRedirect();
        $this->assertDatabaseHas('stock_movements', ['reason' => 'Huile [20L] — Réception huile']);

        $this->post(route('magasinier.mouvements.store', $this->warehouse->id), [
            'movement_date' => '2026-11-14',
            'label' => 'Réception test riz',
            'product' => 'Riz brisé',
            'format' => 25,
            'movement_type' => 'entry',
            'sacs' => 50,
            'kg' => 1250,
        ])->assertRedirect();

        $this->assertDatabaseHas('stock_movements', [
            'warehouse_id' => $this->warehouse->id,
            'type' => 'entry',
            'quantity' => 50,
            'reason' => 'Riz brisé [25kg] — Réception test riz',
        ]);

        // Les deux entrées restent 'en_attente' : le stock officiel ne bouge pas
        $this->assertSame('en_attente', StockMovement::latest('id')->first()->status);
        $this->assertEquals(100, (float) $this->warehouse->fresh()->current_stock);

        // Après vérification (DTL), le stock officiel intègre les +60
        $dtl = $this->makeUser('dtl');
        $svc = app(\App\Services\Magasin\WarehouseStockService::class);
        foreach (StockMovement::where('status', 'en_attente')->get() as $m) {
            $this->assertTrue($svc->verifyPendingMovement($dtl, $m->id)['ok']);
        }
        $this->assertEquals(160, (float) $this->warehouse->fresh()->current_stock);
    }

    public function test_exit_blocked_when_insufficient_stock(): void
    {
        $this->actingAs($this->magasinier, 'magasinier');
        \App\Models\Product::create(['name' => 'Riz brisé', 'unit' => 'kg', 'is_active' => 1]);

        $this->post(route('magasinier.mouvements.store', $this->warehouse->id), [
            'movement_date' => '2026-11-14',
            'label' => 'Sortie excessive', 'product' => 'Riz brisé', 'format' => 25,
            'movement_type' => 'exit',
            'sacs' => 9999,
            'kg' => 9999,
        ])->assertSessionHas('error');

        $this->assertEquals(0, StockMovement::where('type', 'exit')->count());
    }

    public function test_magasinier_cannot_access_unassigned_warehouse(): void
    {
        $other = Warehouse::create([
            'name' => 'Magasin Autre', 'status' => 'active', 'is_active' => true,
            'capacity' => 10, 'current_stock' => 5,
        ]);

        $this->actingAs($this->magasinier, 'magasinier');
        $this->get(route('magasinier.warehouses.show', $other->id))->assertForbidden();
    }

    private function importerSituation(): array
    {
        Warehouse::create(['name' => 'Complexe céréalier de Thiès', 'region' => 'Thiès', 'capacity' => 1, 'current_stock' => 0, 'status' => 'active', 'is_active' => true]);
        return app(\App\Services\Magasin\StockSituationImporter::class)->run(true);
    }

    public function test_real_stock_situation_import_is_exact_and_idempotent(): void
    {
        \App\Models\Product::create(['name' => 'Riz Vallée', 'unit' => 'kg', 'category' => 'Céréales', 'is_active' => 1]);
        // Ancienne donnée de test à remettre à zéro (historique conservé).
        StockMovement::create(['warehouse_id' => $this->warehouse->id, 'type' => 'entry', 'quantity' => 10,
            'quantity_before' => 0, 'quantity_after' => 10, 'reason' => 'Riz brisé [25kg] — test', 'reference' => 'OLD-1']);

        $report = $this->importerSituation();
        $this->assertContains('appliqué', array_column($report, 'status'));

        $svc = app(\App\Services\Magasin\WarehouseStockService::class);
        $dakar = collect($svc->stockStatus($this->warehouse->id)['products'])->keyBy(fn ($p) => $p['product_name'] . '|' . $p['format_kg']);
        $this->assertEquals(692, $dakar['Riz Vallée|50']['total_sacs']);
        $this->assertEquals(34600, $dakar['Riz Vallée|50']['total_kg']);
        $this->assertEquals(28480, $dakar['Huile|20']['total_kg']);
        $this->assertEquals(130, $dakar['Sucre|25']['total_sacs']);
        $this->assertEquals(3080, $dakar['Niébé|5']['total_kg']);
        $this->assertEquals(2595 + 10, $dakar['Farine|15']['total_kg'] + $dakar['Farine|1']['total_kg']);
        $this->assertEquals(0, $dakar['Riz brisé|25']['total_sacs'], 'ancien stock de test remis à zéro');

        $thies = Warehouse::where('region', 'Thiès')->first();
        $t = collect($svc->stockStatus($thies->id)['products'])->keyBy('product_name');
        $this->assertEquals(150000, $t['Riz Vallée']['total_kg']);
        $this->assertEquals(720, $t['Niébé']['total_kg']);

        // Idempotent : une 2e exécution ne recrée rien.
        $before = StockMovement::count();
        app(\App\Services\Magasin\StockSituationImporter::class)->run(true);
        $this->assertSame($before, StockMovement::count());

        // Situation DG temps réel : totaux par produit sur tous les magasins.
        $situation = $svc->situation();
        $riz = collect($situation['totals'])->firstWhere('name', 'Riz Vallée');
        $this->assertEquals(184600, $riz['quantite']);
        $this->assertNotEmpty($situation['recent']);
    }

    public function test_exit_is_checked_against_the_real_stock_of_the_product(): void
    {
        $this->importerSituation();
        $this->actingAs($this->magasinier, 'magasinier');
        $base = ['movement_date' => '2026-11-23', 'label' => 'Distribution', 'product' => 'Sucre', 'format' => 25, 'movement_type' => 'exit', 'kg' => 25];

        // 131 sacs de sucre alors qu'il y en a 130 (même si le riz est abondant) : refusé.
        $this->post(route('magasinier.mouvements.store', $this->warehouse->id), $base + ['sacs' => 131])->assertSessionHas('error');
        $this->post(route('magasinier.mouvements.store', $this->warehouse->id), $base + ['sacs' => 30])->assertRedirect();

        // La sortie reste 'en_attente' : le stock officiel est encore 130
        $svc = app(\App\Services\Magasin\WarehouseStockService::class);
        $sucre = collect($svc->stockStatus($this->warehouse->id)['products'])->firstWhere('product_name', 'Sucre');
        $this->assertEquals(130, $sucre['total_sacs']);

        // Après vérification, le stock officiel passe à 100
        $dtl = $this->makeUser('dtl');
        foreach (StockMovement::where('status', 'en_attente')->get() as $m) {
            $this->assertTrue($svc->verifyPendingMovement($dtl, $m->id)['ok']);
        }
        $sucre = collect($svc->stockStatus($this->warehouse->id)['products'])->firstWhere('product_name', 'Sucre');
        $this->assertEquals(100, $sucre['total_sacs']);
    }
}
