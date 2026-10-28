<?php

namespace Tests\Feature;

use App\Models\EquipmentAssignment;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Nouvelles pages du portail Comptable : tableau de bord, stocks,
 * carburant, affectations matériel, export CSV — + révocation du jeton
 * API à la déconnexion. Schéma minimal SQLite :memory:.
 */
class ComptablePortailTest extends TestCase
{
    protected User $comptable;
    protected User $agent;
    protected Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSchema();

        $this->comptable = $this->makeUser('comptable');
        $this->agent = $this->makeUser('agent');
        $this->warehouse = Warehouse::create([
            'name' => 'Magasin Central',
            'region' => 'Dakar',
            'capacity' => 1000,
            'current_stock' => 150,
            'status' => 'active',
            'is_active' => true,
        ]);
    }

    private function makeUser(string $role): User
    {
        $user = User::create([
            'name' => 'Portal ' . $role,
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
        foreach (['equipment_assignments', 'personal_access_tokens', 'stock_movements', 'stocks', 'warehouses', 'users'] as $t) {
            Schema::dropIfExists($t);
        }

        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->timestamp('email_verified_at')->nullable();
            $t->string('password')->nullable();
            $t->string('role')->default('agent');
            $t->boolean('is_active')->default(true);
            $t->timestamp('last_activity')->nullable();
            $t->rememberToken();
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

        Schema::create('warehouses', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('region')->nullable();
            $t->decimal('capacity', 15, 2)->nullable();
            $t->decimal('current_stock', 15, 2)->nullable();
            $t->string('status')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('stocks', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('warehouse_id')->nullable();
            $t->unsignedBigInteger('stock_type_id')->nullable();
            $t->string('item_name');
            $t->text('description')->nullable();
            $t->decimal('quantity', 15, 2)->default(0);
            $t->decimal('min_quantity', 15, 2)->nullable();
            $t->decimal('max_quantity', 15, 2)->nullable();
            $t->date('expiry_date')->nullable();
            $t->string('batch_number')->nullable();
            $t->string('supplier')->nullable();
            $t->decimal('unit_price', 15, 2)->nullable();
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
            $t->string('produit')->nullable();
            $t->string('unite')->nullable();
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

        Schema::create('equipment_assignments', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('warehouse_id')->nullable();
            $t->string('produit');
            $t->string('unite', 20)->nullable()->default('pièce');
            $t->decimal('quantity', 10, 2)->default(1);
            $t->string('agent_name');
            $t->unsignedBigInteger('assigned_to')->nullable();
            $t->string('numero_bon')->nullable();
            $t->string('status')->default('affectee');
            $t->unsignedBigInteger('assigned_by');
            $t->timestamp('assigned_at')->nullable();
            $t->timestamp('returned_at')->nullable();
            $t->unsignedBigInteger('returned_by')->nullable();
            $t->text('return_note')->nullable();
            $t->timestamps();
        });
    }

    // ===== Tableau de bord =====

    public function test_dashboard_page_loads_for_comptable(): void
    {
        StockMovement::create([
            'warehouse_id' => $this->warehouse->id,
            'type' => 'exit', 'quantity' => 5, 'reference' => 'DASH-1',
            'status' => 'draft', 'created_by' => $this->agent->id,
        ]);

        $this->actingAs($this->comptable, 'comptable')
            ->get('/comptable')
            ->assertOk()
            ->assertSee('Tableau de bord')
            ->assertSee('DASH-1');
    }

    public function test_dashboard_denies_non_comptable(): void
    {
        $this->actingAs($this->agent, 'comptable')
            ->get('/comptable')
            ->assertRedirect(route('comptable.login'));
    }

    // ===== Stocks =====

    public function test_stocks_page_lists_warehouses_and_items(): void
    {
        Stock::create([
            'warehouse_id' => $this->warehouse->id,
            'item_name' => 'Riz 25kg',
            'quantity' => 42,
            'min_quantity' => 10,
        ]);

        $this->actingAs($this->comptable, 'comptable')
            ->get('/comptable/stocks')
            ->assertOk()
            ->assertSee('Magasin Central')
            ->assertSee('Riz 25kg');
    }

    // ===== Carburant =====

    public function test_carburant_page_shows_litres_summary(): void
    {
        StockMovement::create([
            'warehouse_id' => $this->warehouse->id,
            'type' => 'entry', 'quantity' => 500,
            'produit' => 'Gasoil', 'unite' => 'L', 'status' => 'validated',
        ]);
        StockMovement::create([
            'warehouse_id' => $this->warehouse->id,
            'type' => 'exit', 'quantity' => 120,
            'produit' => 'Gasoil', 'unite' => 'L', 'status' => 'validated',
        ]);

        $this->actingAs($this->comptable, 'comptable')
            ->get('/comptable/carburant')
            ->assertOk()
            ->assertSee('Gasoil')
            ->assertSee('380');
    }

    // ===== Matériel =====

    public function test_comptable_can_create_assignment_via_web(): void
    {
        $this->actingAs($this->comptable, 'comptable')
            ->post('/comptable/materiel', [
                'produit' => 'Ordinateur portable',
                'agent_name' => 'Agent Sarr',
                'warehouse_id' => $this->warehouse->id,
                'quantity' => 1,
                'numero_bon' => 'BON-77',
            ])
            ->assertRedirect(route('comptable.materiel'));

        $a = EquipmentAssignment::first();
        $this->assertSame('Agent Sarr', $a->agent_name);
        $this->assertSame('affectee', $a->status);
        $this->assertSame($this->comptable->id, $a->assigned_by);
    }

    public function test_comptable_can_close_assignment_as_returned(): void
    {
        $a = EquipmentAssignment::create([
            'produit' => 'Scanner', 'agent_name' => 'Agent Ba',
            'status' => 'affectee', 'assigned_by' => $this->comptable->id,
            'assigned_at' => now(),
        ]);

        $this->actingAs($this->comptable, 'comptable')
            ->post("/comptable/materiel/{$a->id}/cloturer", [
                'outcome' => 'retournee',
                'return_note' => 'Matériel en bon état.',
            ])
            ->assertRedirect(route('comptable.materiel'));

        $this->assertSame('retournee', $a->fresh()->status);
        $this->assertSame($this->comptable->id, $a->fresh()->returned_by);
    }

    public function test_loss_declaration_requires_note(): void
    {
        $a = EquipmentAssignment::create([
            'produit' => 'Imprimante', 'agent_name' => 'Agent Fall',
            'status' => 'affectee', 'assigned_by' => $this->comptable->id,
            'assigned_at' => now(),
        ]);

        $this->actingAs($this->comptable, 'comptable')
            ->post("/comptable/materiel/{$a->id}/cloturer", [
                'outcome' => 'perdue',
            ]);

        $this->assertSame('affectee', $a->fresh()->status);
    }

    // ===== Export CSV =====

    public function test_csv_export_contains_movement_rows(): void
    {
        StockMovement::create([
            'warehouse_id' => $this->warehouse->id,
            'type' => 'exit', 'quantity' => 7,
            'reference' => 'EXP-CSV-1', 'status' => 'draft',
            'created_by' => $this->agent->id,
        ]);

        $resp = $this->actingAs($this->comptable, 'comptable')
            ->get('/comptable/mouvements/export-csv');

        $resp->assertOk();
        $this->assertStringContainsString('text/csv', $resp->headers->get('Content-Type'));
        $this->assertStringContainsString('EXP-CSV-1', $resp->streamedContent());
    }

    public function test_csv_export_denies_non_comptable(): void
    {
        $this->actingAs($this->agent, 'comptable')
            ->get('/comptable/mouvements/export-csv')
            ->assertRedirect(route('comptable.login'));
    }

    // ===== Parité Web ↔ API =====

    public function test_api_validate_is_visible_on_web(): void
    {
        $m = StockMovement::create([
            'warehouse_id' => $this->warehouse->id,
            'type' => 'exit', 'quantity' => 3,
            'reference' => 'PARITY-1', 'status' => 'draft',
            'created_by' => $this->agent->id,
        ]);

        Sanctum::actingAs($this->comptable, ['comptable']);
        $this->postJson("/api/comptable/v1/movements/{$m->id}/validate")->assertOk();

        $this->actingAs($this->comptable, 'comptable')
            ->get('/comptable/mouvements?status=validated')
            ->assertOk()
            ->assertSee('PARITY-1');
    }

    public function test_web_reject_is_visible_on_api(): void
    {
        $m = StockMovement::create([
            'warehouse_id' => $this->warehouse->id,
            'type' => 'exit', 'quantity' => 3,
            'reference' => 'PARITY-2', 'status' => 'draft',
            'created_by' => $this->agent->id,
        ]);

        $this->actingAs($this->comptable, 'comptable')
            ->post("/comptable/mouvements/{$m->id}/rejeter", [
                'rejection_reason' => 'Justificatif manquant sur la sortie.',
            ]);

        Sanctum::actingAs($this->comptable, ['comptable']);
        $this->getJson("/api/comptable/v1/movements/{$m->id}")
            ->assertOk()
            ->assertJsonPath('movement.status', 'rejected')
            ->assertJsonPath('movement.rejection_reason', 'Justificatif manquant sur la sortie.');
    }

    public function test_rejected_movement_cannot_be_validated(): void
    {
        $m = StockMovement::create([
            'warehouse_id' => $this->warehouse->id,
            'type' => 'exit', 'quantity' => 3,
            'reference' => 'FINAL-1', 'status' => 'rejected',
            'created_by' => $this->agent->id,
        ]);

        Sanctum::actingAs($this->comptable, ['comptable']);
        $this->postJson("/api/comptable/v1/movements/{$m->id}/validate");
        $this->assertSame('rejected', $m->fresh()->status);
    }

    // ===== Déconnexion API =====

    public function test_logout_revokes_current_token(): void
    {
        $token = $this->comptable->createToken('comptable-mobile', ['comptable'])->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/comptable/v1/logout')
            ->assertOk();

        // Le jeton est supprimé en base.
        $this->assertSame(0, \Laravel\Sanctum\PersonalAccessToken::count());

        // Les guards mettent en cache l'utilisateur entre requêtes d'un
        // même test : on les réinitialise pour rejouer une authentification.
        \Illuminate\Support\Facades\Auth::forgetGuards();

        $this->withToken($token)
            ->getJson('/api/comptable/v1/dashboard')
            ->assertUnauthorized();
    }

    public function test_logout_requires_token(): void
    {
        $this->postJson('/api/comptable/v1/logout')->assertUnauthorized();
    }
}
