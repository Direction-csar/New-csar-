<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Stock;
use App\Models\Warehouse;
use App\Models\StockType;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StockManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $warehouse;
    protected $stockType;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un admin
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true
        ]);

        // Créer un entrepôt
        $this->warehouse = Warehouse::create([
            'name' => 'Entrepôt Test',
            'code' => 'TEST-001',
            'address' => 'Dakar, Sénégal',
            'capacity' => 1000,
            'is_active' => true
        ]);

        // Créer un type de stock
        $this->stockType = StockType::create([
            'name' => 'Denrées alimentaires',
            'code' => 'ALIM',
            'description' => 'Produits alimentaires'
        ]);
    }

    /**
     * Test de création d'un produit en stock
     */
    public function test_admin_can_create_stock_item()
    {
        $this->actingAs($this->admin);

        $response = $this->post('/admin/stock', [
            'warehouse_id' => $this->warehouse->id,
            'stock_type_id' => $this->stockType->id,
            'item_name' => 'Riz blanc',
            'quantity' => 100,
            'min_quantity' => 10,
            'max_quantity' => 500,
            'unit_price' => 25000,
            'is_active' => true
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('stocks', [
            'item_name' => 'Riz blanc',
            'quantity' => 100
        ]);
    }

    /**
     * Test d'entrée de stock
     */
    public function test_admin_can_add_stock_entry()
    {
        $this->actingAs($this->admin);

        $stock = Stock::create([
            'warehouse_id' => $this->warehouse->id,
            'stock_type_id' => $this->stockType->id,
            'item_name' => 'Riz blanc',
            'quantity' => 100,
            'min_quantity' => 10,
            'unit_price' => 25000,
            'is_active' => true
        ]);

        $response = $this->post('/admin/stock', [
            'type' => 'in',
            'warehouse_id' => $this->warehouse->id,
            'stock_id' => $stock->id,
            'quantity' => 50,
            'reason' => 'Réapprovisionnement',
            'reference' => 'ENT-2025-001'
        ]);

        $response->assertRedirect();
        
        // Vérifier que le mouvement a été créé
        $this->assertDatabaseHas('stock_movements', [
            'type' => 'in',
            'quantity' => 50,
            'reason' => 'Réapprovisionnement'
        ]);

        // Vérifier que le stock a été mis à jour
        $stock->refresh();
        $this->assertEquals(150, $stock->quantity);
    }

    /**
     * Test de sortie de stock
     */
    public function test_admin_can_remove_stock()
    {
        $this->actingAs($this->admin);

        $stock = Stock::create([
            'warehouse_id' => $this->warehouse->id,
            'stock_type_id' => $this->stockType->id,
            'item_name' => 'Riz blanc',
            'quantity' => 100,
            'min_quantity' => 10,
            'unit_price' => 25000,
            'is_active' => true
        ]);

        $response = $this->post('/admin/stock', [
            'type' => 'out',
            'warehouse_id' => $this->warehouse->id,
            'stock_id' => $stock->id,
            'quantity' => 30,
            'reason' => 'Distribution',
            'reference' => 'SOR-2025-001'
        ]);

        $response->assertRedirect();

        // Vérifier le mouvement
        $this->assertDatabaseHas('stock_movements', [
            'type' => 'out',
            'quantity' => 30
        ]);

        // Vérifier la mise à jour du stock
        $stock->refresh();
        $this->assertEquals(70, $stock->quantity);
    }

    /**
     * Test qu'on ne peut pas sortir plus que le stock disponible
     */
    public function test_cannot_remove_more_than_available_stock()
    {
        $this->actingAs($this->admin);

        $stock = Stock::create([
            'warehouse_id' => $this->warehouse->id,
            'stock_type_id' => $this->stockType->id,
            'item_name' => 'Riz blanc',
            'quantity' => 50,
            'min_quantity' => 10,
            'unit_price' => 25000,
            'is_active' => true
        ]);

        $response = $this->post('/admin/stock', [
            'type' => 'out',
            'warehouse_id' => $this->warehouse->id,
            'stock_id' => $stock->id,
            'quantity' => 100, // Plus que disponible
            'reason' => 'Distribution'
        ]);

        $response->assertSessionHasErrors();
        
        // Le stock ne doit pas avoir changé
        $stock->refresh();
        $this->assertEquals(50, $stock->quantity);
    }

    /**
     * Test d'alerte de stock minimum
     */
    public function test_stock_below_minimum_triggers_alert()
    {
        $this->actingAs($this->admin);

        $stock = Stock::create([
            'warehouse_id' => $this->warehouse->id,
            'stock_type_id' => $this->stockType->id,
            'item_name' => 'Riz blanc',
            'quantity' => 15,
            'min_quantity' => 20, // Seuil minimum
            'unit_price' => 25000,
            'is_active' => true
        ]);

        // Vérifier qu'une notification a été créée
        $this->assertDatabaseHas('notifications', [
            'type' => 'warning',
        ]);
    }

    /**
     * Test de listage des mouvements de stock
     */
    public function test_admin_can_view_stock_movements()
    {
        $this->actingAs($this->admin);

        $stock = Stock::create([
            'warehouse_id' => $this->warehouse->id,
            'stock_type_id' => $this->stockType->id,
            'item_name' => 'Riz blanc',
            'quantity' => 100,
            'is_active' => true
        ]);

        // Créer quelques mouvements
        StockMovement::create([
            'warehouse_id' => $this->warehouse->id,
            'stock_id' => $stock->id,
            'type' => 'in',
            'quantity' => 50,
            'reason' => 'Entrée',
            'created_by' => $this->admin->id
        ]);

        $response = $this->get('/admin/stock');

        $response->assertStatus(200);
        $response->assertSee('Riz blanc');
    }

    /**
     * Test d'export des données de stock
     */
    public function test_admin_can_export_stock_data()
    {
        $this->actingAs($this->admin);

        $response = $this->post('/admin/stock/export', [
            'format' => 'csv'
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
    }

    /**
     * Test de filtrage des mouvements par type
     */
    public function test_can_filter_movements_by_type()
    {
        $this->actingAs($this->admin);

        $stock = Stock::create([
            'warehouse_id' => $this->warehouse->id,
            'stock_type_id' => $this->stockType->id,
            'item_name' => 'Riz blanc',
            'quantity' => 100,
            'is_active' => true
        ]);

        StockMovement::create([
            'warehouse_id' => $this->warehouse->id,
            'stock_id' => $stock->id,
            'type' => 'in',
            'quantity' => 50,
            'reason' => 'Entrée'
        ]);

        $response = $this->get('/admin/stock?type=in');

        $response->assertStatus(200);
        $response->assertSee('Entrée');
    }

    /**
     * Test de recherche de produits
     */
    public function test_can_search_stock_items()
    {
        $this->actingAs($this->admin);

        Stock::create([
            'warehouse_id' => $this->warehouse->id,
            'stock_type_id' => $this->stockType->id,
            'item_name' => 'Riz blanc',
            'quantity' => 100,
            'is_active' => true
        ]);

        $response = $this->get('/admin/stock?search=Riz');

        $response->assertStatus(200);
        $response->assertSee('Riz blanc');
    }
}






































