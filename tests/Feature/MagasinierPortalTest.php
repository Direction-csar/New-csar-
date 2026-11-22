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
        // Le solde est recalculé depuis les mouvements : 0 existant + 50
        $this->assertEquals(60, (float) $this->warehouse->fresh()->current_stock);
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
}
