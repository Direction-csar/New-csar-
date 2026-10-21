<?php

namespace Tests\Feature;

use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\StockAlertService;
use App\Services\StockStatsService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Rendu de la page « Gestion des Stocks » avec les nouvelles statistiques.
 * Schéma minimal SQLite :memory: — jamais la production.
 */
class StockPageRenderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-10-20 10:00:00'));
        $this->createSchema();
    }

    private function createSchema(): void
    {
        foreach (['public_requests', 'notifications', 'messages', 'stock_movements', 'stocks', 'stock_types', 'products', 'warehouses', 'users'] as $t) {
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
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('type')->nullable();
            $t->string('unit')->nullable();
            $t->decimal('unit_price', 10, 2)->nullable();
            $t->string('category')->nullable();
            $t->boolean('is_active')->default(true);
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
        Schema::create('public_requests', function (Blueprint $t) {
            $t->id();
            $t->string('status')->default('pending');
            $t->string('workflow_status')->nullable();
            $t->timestamps();
        });
        Schema::create('notifications', function (Blueprint $t) {
            $t->id();
            $t->boolean('read')->default(false);
            $t->timestamps();
        });
        Schema::create('messages', function (Blueprint $t) {
            $t->id();
            $t->boolean('lu')->default(false);
            $t->timestamps();
        });
    }

    private function seedData(): Warehouse
    {
        $wh = Warehouse::create(['name' => 'Magasin Test', 'status' => 'active', 'is_active' => true]);
        $typeId = DB::table('stock_types')->insertGetId(['name' => 'cereales', 'display_name' => 'Céréales', 'unit' => 'kg']);

        Stock::create([
            'warehouse_id' => $wh->id, 'stock_type_id' => $typeId,
            'item_name' => 'Riz', 'quantity' => 100, 'min_quantity' => 50,
            'unit_price' => 500, 'is_active' => true,
        ]);
        Stock::create([
            'warehouse_id' => $wh->id, 'stock_type_id' => $typeId,
            'item_name' => 'Mil', 'quantity' => 30, 'min_quantity' => 0,
            'unit_price' => null, 'is_active' => true,
        ]);
        StockMovement::forceCreate([
            'warehouse_id' => $wh->id, 'type' => 'in', 'quantity' => 100,
            'quantity_before' => 0, 'quantity_after' => 100,
            'reason' => 'Livraison', 'reference' => 'REF-1',
        ]);
        StockMovement::forceCreate([
            'warehouse_id' => $wh->id, 'type' => 'out', 'quantity' => 20,
            'quantity_before' => 100, 'quantity_after' => 80,
            'reason' => 'Distribution', 'reference' => 'REF-2',
        ]);

        return $wh;
    }

    private function statsFor(?int $warehouseId = null, ?string $from = null, ?string $to = null): array
    {
        $service = app(StockStatsService::class);
        $scope = $warehouseId ? [$warehouseId] : null;
        $moves = $service->movementTotals($scope, $from, $to);
        $totals = $service->stockTotals($scope);

        return [
            'total_movements' => $moves['total_movements'],
            'entrees' => $moves['entrees'],
            'qte_entrees' => $moves['qte_entrees'],
            'sorties' => $moves['sorties'],
            'qte_sorties' => $moves['qte_sorties'],
            'transferts' => $moves['transferts'],
            'ajustements' => $moves['ajustements'],
            'ajustement_net' => $moves['ajustement_net'],
            'valeur_totale' => $totals['valeur'],
            'lots_sans_prix' => $totals['lots_sans_prix'],
            'lots' => $totals['lots'],
            'produits_distincts' => $totals['produits_distincts'],
            'quantites_par_unite' => $totals['by_unit'],
            'evolution_mensuelle' => $service->monthlyEntryVariation($scope),
            'total' => $moves['total_movements'],
        ];
    }

    private function chartDataFor(?int $warehouseId = null): array
    {
        $service = app(StockStatsService::class);
        $scope = $warehouseId ? [$warehouseId] : null;
        return [
            'types' => ['entree' => 1, 'sortie' => 1, 'transfert' => 0, 'ajustement' => 0],
            'entrepots' => $service->movementsByWarehouse($scope),
            'produits' => $service->topProducts($scope),
            'monthly' => $service->monthlySeries($scope),
        ];
    }

    private function renderPage(array $stats, array $chartData, $entrepots): string
    {
        return view('admin.stock.index', [
            'stockAlertPanel' => app(StockAlertService::class)->buildPanel(auth()->user(), []),
            'mouvementsPaginated' => new LengthAwarePaginator([], 0, 15),
            'stats' => $stats,
            'chartData' => $chartData,
            'search' => '',
            'type' => '',
            'mouvement' => '',
            'entrepot' => '',
            'date_debut' => '',
            'date_fin' => '',
            'entrepots' => $entrepots,
        ])->render();
    }

    public function test_page_stock_affiche_stats_reelles(): void
    {
        $wh = $this->seedData();
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $html = $this->renderPage(
            $this->statsFor(),
            $this->chartDataFor(),
            Warehouse::all(['id', 'name'])
        );

        // Valeur réelle : 100 x 500 = 50 000 FCFA
        $this->assertStringContainsString('50 000', $html);
        // Le magasin réel apparaît dans le filtre (plus d'options codées en dur)
        $this->assertStringContainsString('Magasin Test', $html);
        // Lots / produits réels sous Total Mouvements
        $this->assertStringContainsString('2 lots actifs', $html);
        $this->assertStringContainsString('2 produits', $html);
        // Pas de fausses tendances
        $this->assertStringNotContainsString('+15.2%', $html);
        $this->assertStringNotContainsString('+12% ce mois', $html);
        // Couverture de la valorisation affichée
        $this->assertStringContainsString('1 / 2', $html); // 1 lot valorisé sur 2
    }

    public function test_page_stock_etat_sans_prix(): void
    {
        $wh = Warehouse::create(['name' => 'Magasin Vide', 'status' => 'active', 'is_active' => true]);
        Stock::create([
            'warehouse_id' => $wh->id, 'item_name' => 'Riz',
            'quantity' => 10, 'unit_price' => null, 'is_active' => true,
        ]);
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $html = $this->renderPage(
            $this->statsFor(),
            $this->chartDataFor(),
            Warehouse::all(['id', 'name'])
        );

        $this->assertStringContainsString('Non valorisée', $html);
        $this->assertStringNotContainsString('NaN', $html);
    }

    public function test_page_stock_sans_donnees(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $html = $this->renderPage(
            $this->statsFor(),
            ['types' => ['entree' => 0, 'sortie' => 0, 'transfert' => 0, 'ajustement' => 0],
             'entrepots' => [], 'produits' => [],
             'monthly' => ['labels' => [], 'entrees' => [], 'sorties' => []]],
            collect()
        );

        $this->assertStringContainsString('Non valorisée', $html);
        $this->assertStringContainsString('Total Mouvements', $html);
        $this->assertStringNotContainsString('NaN', $html);
    }
}
