<?php

namespace Tests\Feature;

use App\Models\EquipmentAssignment;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * API Comptable — évolution schéma carburant & matériel :
 * soldes carburant (unite='L'), affectations d'équipement,
 * clôture retour/perte avec traçabilité.
 */
class ComptableEquipmentApiTest extends TestCase
{
    protected User $comptable;
    protected User $magasinier;
    protected Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSchema();

        $this->comptable = $this->makeUser('comptable');
        $this->magasinier = $this->makeUser('magasinier');
        $this->warehouse = Warehouse::create([
            'name' => 'Magasin équipement',
            'capacity' => 500,
            'current_stock' => 200,
            'status' => 'active',
            'is_active' => true,
        ]);
    }

    private function makeUser(string $role): User
    {
        $user = User::create([
            'name' => 'EQP ' . $role . ' ' . substr(md5(uniqid()), 0, 4),
            'email' => 'eqp' . $role . substr(md5(uniqid()), 0, 6) . '@csar.test',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $user->role = $role;
        $user->save();
        return $user->refresh();
    }

    private function createSchema(): void
    {
        foreach (['equipment_assignments', 'personal_access_tokens', 'stock_movements', 'warehouses', 'users'] as $t) {
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

        Schema::create('stock_movements', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('warehouse_id');
            $t->string('type');
            $t->string('produit')->nullable();
            $t->string('unite')->nullable();
            $t->decimal('quantity', 15, 2)->default(0);
            $t->string('status')->default('draft');
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

    private function addMovement(string $produit, string $unite, string $type, float $qty): void
    {
        StockMovement::create([
            'warehouse_id' => $this->warehouse->id,
            'produit' => $produit,
            'unite' => $unite,
            'type' => $type,
            'quantity' => $qty,
            'status' => 'validated',
        ]);
    }

    /** @test Le résumé carburant agrège entrées/sorties par produit et magasin */
    public function test_fuel_summary_aggregates_litres(): void
    {
        Sanctum::actingAs($this->comptable, ['comptable']);

        $this->addMovement('Gasoil', 'L', 'entry', 500);
        $this->addMovement('Gasoil', 'L', 'exit', 120);
        $this->addMovement('Essence', 'L', 'entry', 100);
        $this->addMovement('Riz', 'kg', 'entry', 50); // ignoré : pas 'L'

        $resp = $this->getJson('/api/comptable/v1/fuel/summary')->assertOk();

        $data = collect($resp->json());
        $gasoil = $data->firstWhere('produit', 'Gasoil');
        $essence = $data->firstWhere('produit', 'Essence');

        $this->assertEquals(500.0, $gasoil['total_in']);
        $this->assertEquals(120.0, $gasoil['total_out']);
        $this->assertEquals(380.0, $gasoil['balance']);
        $this->assertEquals(100.0, $essence['balance']);
        $this->assertNull($data->firstWhere('produit', 'Riz'));
    }

    /** @test Le résumé carburant exige le rôle comptable */
    public function test_fuel_summary_denies_non_comptable(): void
    {
        Sanctum::actingAs($this->magasinier, ['comptable']);
        $this->getJson('/api/comptable/v1/fuel/summary')->assertForbidden();
    }

    /** @test Le comptable crée une affectation de matériel */
    public function test_store_equipment_assignment(): void
    {
        Sanctum::actingAs($this->comptable, ['comptable']);

        $this->postJson('/api/comptable/v1/equipment-assignments', [
            'produit' => 'Ordinateur portable',
            'agent_name' => 'Agent Diallo',
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 1,
            'numero_bon' => 'BON-2026-001',
        ])->assertCreated()->assertJsonPath('assignment.status', 'affectee');

        $a = EquipmentAssignment::first();
        $this->assertSame('Agent Diallo', $a->agent_name);
        $this->assertSame($this->comptable->id, $a->assigned_by);
        $this->assertNotNull($a->assigned_at);
    }

    /** @test La liste filtre par statut et recherche texte */
    public function test_equipment_list_filters(): void
    {
        Sanctum::actingAs($this->comptable, ['comptable']);

        EquipmentAssignment::create([
            'produit' => 'Clé USB', 'agent_name' => 'Agent Ndiaye',
            'status' => 'affectee', 'assigned_by' => $this->comptable->id,
            'assigned_at' => now(),
        ]);
        EquipmentAssignment::create([
            'produit' => 'Chaise', 'agent_name' => 'Agent Fall',
            'status' => 'retournee', 'assigned_by' => $this->comptable->id,
            'assigned_at' => now(), 'returned_at' => now(),
        ]);

        $resp = $this->getJson('/api/comptable/v1/equipment-assignments?status=affectee&q=Ndiaye')->assertOk();
        $data = $resp->json('data');
        $this->assertCount(1, $data);
        $this->assertSame('Clé USB', $data[0]['produit']);
    }

    /** @test Clôture retour : statut + traçabilité enregistrés */
    public function test_close_assignment_return(): void
    {
        Sanctum::actingAs($this->comptable, ['comptable']);

        $a = EquipmentAssignment::create([
            'produit' => 'Vidéoprojecteur', 'agent_name' => 'Agent Sarr',
            'status' => 'affectee', 'assigned_by' => $this->comptable->id,
            'assigned_at' => now(),
        ]);

        $this->postJson("/api/comptable/v1/equipment-assignments/{$a->id}/close", [
            'outcome' => 'retournee',
            'return_note' => 'Matériel en bon état.',
        ])->assertOk()->assertJsonPath('assignment.status', 'retournee');

        $this->assertNotNull($a->fresh()->returned_at);
        $this->assertSame($this->comptable->id, $a->fresh()->returned_by);
    }

    /** @test Déclarer une perte exige un motif */
    public function test_close_assignment_loss_requires_note(): void
    {
        Sanctum::actingAs($this->comptable, ['comptable']);

        $a = EquipmentAssignment::create([
            'produit' => 'Scanner', 'agent_name' => 'Agent Ba',
            'status' => 'affectee', 'assigned_by' => $this->comptable->id,
            'assigned_at' => now(),
        ]);

        $this->postJson("/api/comptable/v1/equipment-assignments/{$a->id}/close", [
            'outcome' => 'perdue',
        ])->assertStatus(422);

        $this->assertSame('affectee', $a->fresh()->status);
    }

    /** @test Une affectation clôturée ne peut pas être reclôturée */
    public function test_closed_assignment_cannot_reclose(): void
    {
        Sanctum::actingAs($this->comptable, ['comptable']);

        $a = EquipmentAssignment::create([
            'produit' => 'Imprimante', 'agent_name' => 'Agent Gueye',
            'status' => 'retournee', 'assigned_by' => $this->comptable->id,
            'assigned_at' => now(), 'returned_at' => now(),
        ]);

        $this->postJson("/api/comptable/v1/equipment-assignments/{$a->id}/close", [
            'outcome' => 'retournee',
        ])->assertStatus(422);
    }

    /** @test Les endpoints équipement exigent le rôle comptable */
    public function test_equipment_endpoints_deny_non_comptable(): void
    {
        Sanctum::actingAs($this->magasinier, ['comptable']);

        $this->getJson('/api/comptable/v1/equipment-assignments')->assertForbidden();
        $this->postJson('/api/comptable/v1/equipment-assignments', [
            'produit' => 'X', 'agent_name' => 'Y',
        ])->assertForbidden();
    }
}
