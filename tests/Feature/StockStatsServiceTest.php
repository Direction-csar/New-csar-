<?php

namespace Tests\Feature;

use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\StockStatsService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Statistiques de stock (StockStatsService) — lecture seule.
 * Schéma minimal sur SQLite :memory: — jamais la production.
 */
class StockStatsServiceTest extends TestCase
{
    private StockStatsService $service;
    private Warehouse $whA;
    private Warehouse $whB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-10-20 10:00:00'));
        $this->createSchema();

        $this->service = app(StockStatsService::class);
        $this->whA = Warehouse::create(['name' => 'Magasin A', 'status' => 'active', 'is_active' => true]);
        $this->whB = Warehouse::create(['name' => 'Magasin B', 'status' => 'active', 'is_active' => true]);
    }

    private function createSchema(): void
    {
        foreach (['stock_movements', 'stocks', 'stock_types', 'warehouses', 'users'] as $t) {
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
            $t->string('status')->default('active');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('stock_types', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('display_name')->nullable();
            $t->string('unit')->nullable();
            $t->timestamps();
        });
        Schema::create('stocks', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('warehouse_id');
            $t->unsignedBigInteger('stock_type_id')->nullable();
            $t->string('item_name');
            $t->decimal('quantity', 10, 2);
            $t->decimal('min_quantity', 10, 2)->default(0);
            $t->date('expiry_date')->nullable();
            $t->decimal('unit_price', 10, 2)->nullable();
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
            $t->string('reference')->nullable();
            $t->string('numero_transfert')->nullable();
            $t->text('metadata')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
        });
    }

    private function makeType(string $name, string $display, string $unit): int
    {
        return DB::table('stock_types')->insertGetId([
            'name' => $name, 'display_name' => $display, 'unit' => $unit,
        ]);
    }

    private function makeStock(int $wh, string $item, float $qty, ?int $typeId = null, ?float $price = null, bool $active = true, ?float $min = 0): Stock
    {
        return Stock::create([
            'warehouse_id' => $wh,
            'stock_type_id' => $typeId,
            'item_name' => $item,
            'quantity' => $qty,
            'min_quantity' => $min,
            'unit_price' => $price,
            'is_active' => $active,
        ]);
    }

    private function makeMovement(int $wh, string $type, float $qty, array $extra = []): StockMovement
    {
        return StockMovement::forceCreate(array_merge([
            'warehouse_id' => $wh,
            'type' => $type,
            'quantity' => $qty,
            'quantity_before' => 0,
            'quantity_after' => 0,
            'reason' => 'test',
            'reference' => 'REF-' . uniqid(),
        ], $extra));
    }

    /* ------------------------------------------------------------ */

    public function test_totaux_distinguent_lots_produits_et_quantite_par_unite(): void
    {
        $cereales = $this->makeType('cereales', 'Céréales', 'kg');
        $carburant = $this->makeType('carburant', 'Carburant', 'L');

        // Même produit, deux lots → produits distincts = 1 pour cet item
        $this->makeStock($this->whA->id, 'Riz', 100, $cereales);
        $this->makeStock($this->whB->id, 'Riz', 50, $cereales);
        $this->makeStock($this->whA->id, 'Sucre', 30, $cereales);
        $this->makeStock($this->whA->id, 'Gasoil', 200, $carburant);
        $this->makeStock($this->whA->id, 'Inactif', 999, $cereales, null, false); // lot inactif exclu

        $totals = $this->service->stockTotals();

        $this->assertSame(4, $totals['lots']);
        $this->assertSame(3, $totals['produits_distincts']); // Riz + Sucre + Gasoil
        $this->assertSame(2, $totals['magasins']);
        $this->assertEqualsWithDelta(180.0, $totals['by_unit']['kg'], 0.01);  // 100+50+30
        $this->assertEqualsWithDelta(200.0, $totals['by_unit']['L'], 0.01);
        $this->assertArrayNotHasKey('unité', $totals['by_unit']);
    }

    public function test_valeur_reelle_et_lots_sans_prix(): void
    {
        $type = $this->makeType('cereales', 'Céréales', 'kg');
        $this->makeStock($this->whA->id, 'Riz', 10, $type, 500);   // 5 000
        $this->makeStock($this->whA->id, 'Mil', 20, $type, 250);   // 5 000
        $this->makeStock($this->whA->id, 'Maïs', 40, $type, null); // sans prix

        $totals = $this->service->stockTotals();

        $this->assertSame(10000.0, $totals['valeur']);
        $this->assertSame(1, $totals['lots_sans_prix']);
    }

    public function test_valeur_null_quand_aucun_prix_renseigne(): void
    {
        $type = $this->makeType('cereales', 'Céréales', 'kg');
        $this->makeStock($this->whA->id, 'Riz', 10, $type, null);

        $this->assertNull($this->service->stockTotals()['valeur']);
    }

    public function test_mouvements_sur_periode_et_quantites(): void
    {
        $this->makeMovement($this->whA->id, 'in', 100, ['created_at' => '2026-10-05 08:00:00']);
        $this->makeMovement($this->whA->id, 'out', 30, ['created_at' => '2026-10-10 08:00:00']);
        $this->makeMovement($this->whA->id, 'out', 20, ['created_at' => '2026-09-15 08:00:00']); // hors période
        $this->makeMovement($this->whB->id, 'entry', 40, ['created_at' => '2026-10-12 08:00:00']);

        $totals = $this->service->movementTotals(null, '2026-10-01', '2026-10-31');

        $this->assertSame(3, $totals['total_movements']);
        $this->assertSame(2, $totals['entrees']);
        $this->assertEqualsWithDelta(140.0, $totals['qte_entrees'], 0.01);
        $this->assertSame(1, $totals['sorties']);
        $this->assertEqualsWithDelta(30.0, $totals['qte_sorties'], 0.01);
    }

    public function test_transferts_non_comptes_deux_fois(): void
    {
        // Transfert 50 sacs A → B : jambe source 'transfer' + jambe destination 'entry'
        $this->makeMovement($this->whA->id, 'transfer', 50, [
            'reason' => 'Riz (Transfert vers: Magasin B)',
            'numero_transfert' => 'T-001',
        ]);
        $this->makeMovement($this->whB->id, 'entry', 50, [
            'reason' => 'Riz (Transfert depuis: Magasin A)',
            'numero_transfert' => 'T-001',
            'metadata' => json_encode(['transfer_type' => 'in']),
        ]);
        // Vraie entrée fournisseur
        $this->makeMovement($this->whA->id, 'in', 200, ['reason' => 'Livraison']);

        $totals = $this->service->movementTotals();

        $this->assertSame(1, $totals['transferts']);
        $this->assertSame(1, $totals['entrees']);                        // jambe destination exclue
        $this->assertEqualsWithDelta(200.0, $totals['qte_entrees'], 0.01); // pas 250
        $this->assertSame(3, $totals['total_movements']);
    }

    public function test_jambe_transfert_detectee_par_reason_seule(): void
    {
        // Sans numero_transfert ni metadata : le motif doit suffire
        $this->makeMovement($this->whB->id, 'entry', 50, [
            'reason' => 'Mil (Transfert depuis: Magasin A)',
        ]);

        $totals = $this->service->movementTotals();
        $this->assertSame(0, $totals['entrees']);
    }

    public function test_ajustement_net_signe(): void
    {
        $this->makeMovement($this->whA->id, 'ajustement', 10, ['quantity_before' => 100, 'quantity_after' => 110]);
        $this->makeMovement($this->whA->id, 'adjustment', 5, ['quantity_before' => 110, 'quantity_after' => 105]);

        $totals = $this->service->movementTotals();
        $this->assertSame(2, $totals['ajustements']);
        $this->assertEqualsWithDelta(5.0, $totals['ajustement_net'], 0.01); // +10 -5
    }

    public function test_portee_magasin_par_role(): void
    {
        $type = $this->makeType('cereales', 'Céréales', 'kg');
        $this->makeStock($this->whA->id, 'Riz', 100, $type);
        $this->makeStock($this->whB->id, 'Sucre', 40, $type);

        $magasinier = User::factory()->create(['role' => 'magasinier', 'warehouse_id' => $this->whB->id]);
        $scope = $this->service->warehouseScope($magasinier);

        $this->assertSame([$this->whB->id], $scope);

        $totals = $this->service->stockTotals($scope);
        $this->assertSame(1, $totals['lots']);
        $this->assertEqualsWithDelta(40.0, $totals['by_unit']['kg'], 0.01);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->assertNull($this->service->warehouseScope($admin)); // tous les magasins
    }

    public function test_param_entrepot_hors_perimetre_refuse(): void
    {
        // Un magasinier limité à B ne peut pas lire les mouvements de A
        // en passant ?entrepot=A : scopedWarehouseIds() doit renvoyer [].
        $magasinier = User::factory()->create(['role' => 'magasinier', 'warehouse_id' => $this->whB->id]);

        $request = \Illuminate\Http\Request::create('/admin/stock', 'GET', ['entrepot' => $this->whA->id]);
        $request->setUserResolver(fn () => $magasinier);

        $controller = new \App\Http\Controllers\Admin\StockController();
        $method = new \ReflectionMethod($controller, 'scopedWarehouseIds');
        $method->setAccessible(true);

        $this->assertSame([], $method->invoke($controller, $request));

        // Sans paramètre : périmètre = son magasin uniquement
        $request = \Illuminate\Http\Request::create('/admin/stock', 'GET');
        $request->setUserResolver(fn () => $magasinier);
        $this->assertSame([$this->whB->id], $method->invoke($controller, $request));

        // Admin : périmètre global, filtre entrepot accepté
        $admin = User::factory()->create(['role' => 'admin']);
        $request = \Illuminate\Http\Request::create('/admin/stock', 'GET', ['entrepot' => $this->whA->id]);
        $request->setUserResolver(fn () => $admin);
        $this->assertSame([$this->whA->id], $method->invoke($controller, $request));
    }

    public function test_repartition_par_magasin_et_categorie(): void
    {
        $cereales = $this->makeType('cereales', 'Céréales', 'kg');
        $huile = $this->makeType('huile', 'Huiles', 'L');
        $this->makeStock($this->whA->id, 'Riz', 100, $cereales);
        $this->makeStock($this->whB->id, 'Riz', 50, $cereales);
        $this->makeStock($this->whB->id, 'Huile', 80, $huile);
        $this->makeStock($this->whA->id, 'Ghost', 999, $cereales, null, false); // inactif

        $parMagasin = $this->service->stockByWarehouse();
        $this->assertEqualsWithDelta(100.0, $parMagasin['Magasin A'], 0.01);
        $this->assertEqualsWithDelta(130.0, $parMagasin['Magasin B'], 0.01);

        $parCat = $this->service->stockByCategory();
        $this->assertEqualsWithDelta(150.0, $parCat['Céréales'], 0.01);
        $this->assertEqualsWithDelta(80.0, $parCat['Huiles'], 0.01);
    }

    public function test_top_produits_par_quantite(): void
    {
        $type = $this->makeType('cereales', 'Céréales', 'kg');
        $this->makeStock($this->whA->id, 'Riz', 100, $type);
        $this->makeStock($this->whB->id, 'Riz', 60, $type);
        $this->makeStock($this->whA->id, 'Sucre', 30, $type);
        $this->makeStock($this->whA->id, 'Vide', 0, $type); // qty 0 exclu

        $top = $this->service->topProducts();
        $this->assertEqualsWithDelta(160.0, $top['Riz'], 0.01);
        $this->assertEqualsWithDelta(30.0, $top['Sucre'], 0.01);
        $this->assertArrayNotHasKey('Vide', $top);
    }

    public function test_serie_mensuelle_exclut_jambes_de_transfert(): void
    {
        $this->makeMovement($this->whA->id, 'in', 100, ['created_at' => '2026-10-05 08:00:00']);
        $this->makeMovement($this->whA->id, 'out', 25, ['created_at' => '2026-10-10 08:00:00']);
        $this->makeMovement($this->whB->id, 'entry', 50, [
            'created_at' => '2026-10-12 08:00:00',
            'reason' => 'Riz (Transfert depuis: Magasin A)',
            'numero_transfert' => 'T-9',
        ]);

        $series = $this->service->monthlySeries(null, 6);
        $this->assertCount(6, $series['labels']);
        $this->assertCount(6, $series['entrees']);

        $october = array_search(now()->format('Y-m'), array_map(
            fn ($i) => now()->startOfMonth()->subMonths(5 - $i)->format('Y-m'),
            range(0, 5)
        ));
        // Le mois courant est le dernier point de la série
        $this->assertEqualsWithDelta(100.0, end($series['entrees']), 0.01);
        $this->assertEqualsWithDelta(25.0, end($series['sorties']), 0.01);
    }

    public function test_low_stock_count_coherent_avec_alertes(): void
    {
        $type = $this->makeType('cereales', 'Céréales', 'kg');
        $this->makeStock($this->whA->id, 'Vide', 0, $type, null, true, 0);       // épuisé
        $this->makeStock($this->whA->id, 'Bas', 40, $type, null, true, 200);     // sous seuil
        $this->makeStock($this->whA->id, 'OK', 900, $type, null, true, 100);     // normal
        $this->makeStock($this->whA->id, 'Off', 0, $type, null, false, 0);       // inactif exclu

        $this->assertSame(2, $this->service->lowStockLotsCount());
    }

    public function test_cas_sans_donnees(): void
    {
        $totals = $this->service->stockTotals();
        $this->assertSame(0, $totals['lots']);
        $this->assertSame(0, $totals['produits_distincts']);
        $this->assertSame([], $totals['by_unit']);
        $this->assertNull($totals['valeur']);

        $moves = $this->service->movementTotals();
        $this->assertSame(0, $moves['total_movements']);
        $this->assertSame(0.0, $moves['qte_entrees']);

        $this->assertSame([], $this->service->stockByWarehouse());
        $this->assertSame([], $this->service->stockByCategory());
        $this->assertSame([], $this->service->topProducts());
        $this->assertNull($this->service->monthlyEntryVariation());

        $series = $this->service->monthlySeries(null, 6);
        $this->assertSame([0.0, 0.0, 0.0, 0.0, 0.0, 0.0], $series['entrees']);
    }

    public function test_scope_vide_ne_renvoie_rien(): void
    {
        $type = $this->makeType('cereales', 'Céréales', 'kg');
        $this->makeStock($this->whA->id, 'Riz', 100, $type);
        $this->makeMovement($this->whA->id, 'in', 50);

        // [] = aucun magasin autorisé
        $totals = $this->service->stockTotals([]);
        $this->assertSame(0, $totals['lots']);

        $moves = $this->service->movementTotals([]);
        $this->assertSame(0, $moves['total_movements']);
    }
}
