<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\StockAlertService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Alertes de stock calculées (lecture seule) à partir de la table stocks.
 * Schéma minimal créé sur SQLite :memory: — jamais la production.
 */
class StockAlertServiceTest extends TestCase
{
    private StockAlertService $service;
    private User $admin;
    private Warehouse $whA;
    private Warehouse $whB;
    private int $typeId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-10-20 10:00:00'));
        $this->createSchema();

        $this->service = app(StockAlertService::class);
        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->whA = Warehouse::create(['name' => 'Magasin A', 'status' => 'active', 'is_active' => true]);
        $this->whB = Warehouse::create(['name' => 'Magasin B', 'status' => 'active', 'is_active' => true]);
        $this->typeId = DB::table('stock_types')->insertGetId(['name' => 'Céréales', 'unit' => 'kg']);
    }

    private function createSchema(): void
    {
        foreach (['stock_alerts', 'stock_movements', 'stocks', 'stock_types', 'products', 'warehouses', 'users'] as $t) {
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
            $t->string('unit')->nullable();
            $t->timestamps();
        });
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('type')->nullable();
            $t->string('unit')->nullable();
            $t->text('description')->nullable();
            $t->decimal('unit_price', 10, 2)->nullable();
            $t->string('category')->nullable();
            $t->string('image')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('stocks', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('warehouse_id');
            $t->unsignedBigInteger('stock_type_id')->nullable();
            $t->string('item_name');
            $t->text('description')->nullable();
            $t->decimal('quantity', 10, 2);
            $t->decimal('min_quantity', 10, 2)->default(0);
            $t->decimal('max_quantity', 10, 2)->nullable();
            $t->date('expiry_date')->nullable();
            $t->string('batch_number')->nullable();
            $t->string('supplier')->nullable();
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
            $t->timestamps();
        });
        Schema::create('stock_alerts', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('stock_id');
            $t->unsignedBigInteger('warehouse_id');
            $t->string('alert_type');
            $t->string('severity');
            $t->text('message');
            $t->decimal('current_quantity', 10, 2);
            $t->decimal('threshold_quantity', 10, 2);
            $t->boolean('is_resolved')->default(false);
            $t->timestamps();
        });
    }

    private function lot(array $attrs): Stock
    {
        return Stock::create(array_merge([
            'warehouse_id' => $this->whA->id,
            'stock_type_id' => $this->typeId,
            'item_name' => 'Riz',
            'quantity' => 100,
            'min_quantity' => 20,
            'is_active' => true,
        ], $attrs));
    }

    private function alerts(?User $user = null, array $filters = [])
    {
        return $this->service->getActiveAlerts($user ?? $this->admin, $filters);
    }

    public function test_stock_nul_donne_une_alerte_epuise_critique(): void
    {
        $this->lot(['quantity' => 0]);

        $alerts = $this->alerts();

        $this->assertCount(1, $alerts);
        $this->assertSame('out_of_stock', $alerts[0]['type']);
        $this->assertSame('critical', $alerts[0]['priority']);
        $this->assertSame(0.0, $alerts[0]['quantity']);
        $this->assertSame(20.0, $alerts[0]['threshold']);
        $this->assertSame('Magasin A', $alerts[0]['warehouse_name']);
    }

    public function test_stock_epuise_sans_seuil_reste_signale(): void
    {
        $this->lot(['quantity' => 0, 'min_quantity' => 0]);

        $this->assertSame(['out_of_stock'], $this->alerts()->pluck('type')->all());
    }

    public function test_stock_inferieur_ou_egal_au_seuil(): void
    {
        $this->lot(['item_name' => 'Egal', 'quantity' => 20, 'min_quantity' => 20]);
        $this->lot(['item_name' => 'Bas', 'quantity' => 8, 'min_quantity' => 20]);

        $alerts = $this->alerts()->keyBy('product_name');

        $this->assertSame('low_stock', $alerts['Egal']['type']);
        $this->assertSame('warning', $alerts['Egal']['priority']);
        $this->assertSame('low_stock', $alerts['Bas']['type']);
        $this->assertSame('critical', $alerts['Bas']['priority']);
    }

    public function test_stock_normal_ne_donne_aucune_alerte(): void
    {
        $this->lot(['quantity' => 21, 'min_quantity' => 20]);
        $this->lot(['item_name' => 'Sans seuil', 'quantity' => 5, 'min_quantity' => 0]);
        $this->lot(['item_name' => 'Loin', 'expiry_date' => '2026-12-31']);

        $this->assertCount(0, $this->alerts());
    }

    public function test_peremption_proche(): void
    {
        $this->lot(['item_name' => 'J+20', 'expiry_date' => '2026-11-09']);
        $this->lot(['item_name' => 'J+5', 'expiry_date' => '2026-10-25']);
        $this->lot(['item_name' => 'J+31', 'expiry_date' => '2026-11-20']);

        $alerts = $this->alerts()->keyBy('product_name');

        $this->assertCount(2, $alerts);
        $this->assertSame('expiring_soon', $alerts['J+20']['type']);
        $this->assertSame('warning', $alerts['J+20']['priority']);
        $this->assertSame(20, $alerts['J+20']['days_left']);
        $this->assertSame('2026-11-09', $alerts['J+20']['expiry_date']);
        $this->assertSame('critical', $alerts['J+5']['priority']);
    }

    public function test_produit_perime(): void
    {
        $this->lot(['item_name' => 'Hier', 'expiry_date' => '2026-10-19']);
        $this->lot(['item_name' => 'Vide perime', 'quantity' => 0, 'min_quantity' => 0, 'expiry_date' => '2026-01-01']);

        $alerts = $this->alerts()->keyBy('product_name');

        $this->assertSame('expired', $alerts['Hier']['type']);
        $this->assertSame('critical', $alerts['Hier']['priority']);
        $this->assertSame(-1, $alerts['Hier']['days_left']);
        // Lot vide : seule l'alerte d'épuisement, pas de péremption sur zéro unité
        $this->assertSame('out_of_stock', $alerts['Vide perime']['type']);
        $this->assertCount(1, $this->alerts()->where('product_name', 'Vide perime'));
    }

    public function test_pas_de_doublon_par_lot(): void
    {
        $this->lot(['quantity' => 5, 'min_quantity' => 20, 'expiry_date' => '2026-10-01']);

        $types = $this->alerts()->pluck('type')->sort()->values()->all();

        $this->assertSame(['expired', 'low_stock'], $types);
    }

    public function test_lot_inactif_ignore(): void
    {
        $this->lot(['quantity' => 0, 'is_active' => false]);

        $this->assertCount(0, $this->alerts());
    }

    public function test_filtres_magasin_categorie_priorite(): void
    {
        $this->lot(['item_name' => 'A0', 'quantity' => 0]);
        $this->lot(['item_name' => 'B-bas', 'warehouse_id' => $this->whB->id, 'quantity' => 18, 'min_quantity' => 20]);
        $this->lot(['item_name' => 'B-perime', 'warehouse_id' => $this->whB->id, 'expiry_date' => '2026-10-10']);

        $this->assertSame(['A0'], $this->alerts(null, ['warehouse_id' => $this->whA->id])->pluck('product_name')->all());
        $this->assertSame(['B-perime'], $this->alerts(null, ['type' => 'expired'])->pluck('product_name')->all());
        $this->assertSame(['B-bas'], $this->alerts(null, ['priority' => 'warning'])->pluck('product_name')->all());
        $this->assertCount(3, $this->alerts(null, ['type' => 'inconnu', 'priority' => 'x']));
    }

    public function test_isolation_par_magasin_et_permissions(): void
    {
        $this->lot(['item_name' => 'A0', 'quantity' => 0]);
        $this->lot(['item_name' => 'B0', 'warehouse_id' => $this->whB->id, 'quantity' => 0]);

        $magA = User::factory()->create(['role' => 'magasinier', 'warehouse_id' => $this->whA->id]);
        $sansMagasin = User::factory()->create(['role' => 'magasinier', 'warehouse_id' => null]);

        $this->assertSame(['A0'], $this->alerts($magA)->pluck('product_name')->all());
        $this->assertCount(0, $this->alerts($magA, ['warehouse_id' => $this->whB->id]));
        $this->assertSame([$this->whA->id], $this->service->accessibleWarehouses($magA)->pluck('id')->all());
        $this->assertCount(0, $this->alerts($sansMagasin));
        $this->assertCount(0, $this->service->getActiveAlerts(null));
        $this->assertCount(2, $this->alerts($this->admin));
        $this->assertCount(2, $this->service->accessibleWarehouses($this->admin));
    }

    public function test_aucune_donnee_fictive_et_lecture_seule(): void
    {
        $empty = $this->service->summarize($this->alerts());
        $this->assertSame(0, $empty['total']);
        $this->assertSame(0, $empty['critical']);
        $this->assertSame(0, $empty['warning']);
        $this->assertSame([0, 0, 0, 0], array_values($empty['by_type']));

        $this->lot(['quantity' => 0]);
        $huile = $this->lot(['item_name' => 'Huile', 'quantity' => 10, 'min_quantity' => 30, 'expiry_date' => '2026-10-22']);

        $summary = $this->service->summarize($this->alerts());

        $this->assertSame(3, $summary['total']);
        $this->assertSame(3, $summary['critical']);
        $this->assertSame(1, $summary['by_type']['out_of_stock']);
        $this->assertSame(1, $summary['by_type']['low_stock']);
        $this->assertSame(1, $summary['by_type']['expiring_soon']);
        $this->assertSame(0, $summary['by_type']['expired']);

        $this->assertSame(10.0, (float) $huile->fresh()->quantity);
        $this->assertSame(30.0, (float) $huile->fresh()->min_quantity);
        $this->assertSame(0, DB::table('stock_alerts')->count());
        $this->assertSame(0, DB::table('stock_movements')->count());
    }

    private function renderPanel(?User $user, array $query = []): string
    {
        return view('admin.partials.stock-alerts', [
            'stockAlertPanel' => $this->service->buildPanel($user, $query),
            'stockAlertAction' => route('admin.dashboard'),
        ])->render();
    }

    public function test_panneau_etat_vide_sans_donnees_fictives(): void
    {
        $html = $this->renderPanel($this->admin);

        $this->assertStringContainsString('Aucune alerte de stock', $html);
        $this->assertStringNotContainsString('<table', $html);
        preg_match_all('/class="sa-tile-value">(\d+)</', $html, $m);
        $this->assertSame(['0', '0', '0', '0', '0', '0'], $m[1]);
    }

    public function test_panneau_indisponible_si_calcul_en_echec(): void
    {
        $html = view('admin.partials.stock-alerts', ['stockAlertPanel' => null])->render();

        $this->assertStringContainsString('Alertes indisponibles', $html);
        $this->assertStringNotContainsString('sa-tile-value', $html);
    }

    public function test_panneau_liste_filtres_et_liens(): void
    {
        $product = Product::create(['name' => 'Riz', 'unit' => 'kg', 'is_active' => true]);
        $this->lot(['quantity' => 0, 'batch_number' => 'L-01']);
        $this->lot(['item_name' => '<script>x</script>', 'warehouse_id' => $this->whB->id, 'expiry_date' => '2026-11-05']);

        $html = $this->renderPanel($this->admin);
        $this->assertStringContainsString('href="' . route('admin.products.show', $product->id) . '"', $html);
        $this->assertStringContainsString('Lot L-01', $html);
        $this->assertStringContainsString('Seuil : 20 kg', $html);
        $this->assertStringContainsString('Expire le 05/11/2026', $html);
        $this->assertStringContainsString('&lt;script&gt;x&lt;/script&gt;', $html);
        $this->assertStringNotContainsString('<script>x</script>', $html);
        $this->assertStringContainsString('sa-badge-critical', $html);
        $this->assertStringContainsString('sa-badge-warning', $html);

        $filtered = $this->renderPanel($this->admin, ['alert_warehouse' => $this->whB->id, 'alert_type' => 'out_of_stock']);
        $this->assertStringContainsString('Aucune alerte ne correspond aux filtres', $filtered);
        $this->assertStringContainsString('Réinitialiser', $filtered);
    }

    public function test_panneau_magasinier_ne_voit_que_son_magasin(): void
    {
        $this->lot(['item_name' => 'Produit A', 'quantity' => 0]);
        $this->lot(['item_name' => 'Produit B', 'warehouse_id' => $this->whB->id, 'quantity' => 0]);
        $magA = User::factory()->create(['role' => 'magasinier', 'warehouse_id' => $this->whA->id]);

        $html = $this->renderPanel($magA, ['alert_warehouse' => $this->whB->id]);

        $this->assertStringContainsString('Produit A', $html);
        $this->assertStringNotContainsString('Produit B', $html);
        $this->assertStringNotContainsString('Magasin B', $html);
    }

    public function test_lien_vers_fiche_produit_ou_page_stock(): void
    {
        $product = Product::create(['name' => 'Riz', 'unit' => 'kg', 'is_active' => true]);
        $this->lot(['item_name' => 'riz ', 'quantity' => 0]);
        $this->lot(['item_name' => 'Inconnu', 'quantity' => 0]);

        $alerts = $this->alerts()->keyBy('product_name');

        $this->assertSame(route('admin.products.show', $product->id), $alerts['riz ']['url']);
        $this->assertSame(route('admin.stock.index', ['entrepot' => $this->whA->id]), $alerts['Inconnu']['url']);
    }
}
