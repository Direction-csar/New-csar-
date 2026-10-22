<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

/**
 * Tests du portail Comptable des matières : validation/rejet des
 * mouvements de stock (draft → validated | rejected).
 * Schéma minimal SQLite :memory: comme WarehouseKeeperApiTest.
 */
class ComptableMouvementsTest extends TestCase
{
    protected User $comptable;
    protected User $agent;
    protected Warehouse $warehouse;
    protected StockMovement $mouvement;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSchema();

        $this->comptable = User::factory()->create([
            'role' => 'comptable',
            'is_active' => true,
        ]);
        $this->agent = User::factory()->create([
            'role' => 'agent',
            'is_active' => true,
        ]);
        $this->warehouse = Warehouse::create([
            'name' => 'Magasin Compta',
            'capacity' => 1000,
            'current_stock' => 0,
            'status' => 'active',
            'is_active' => true,
        ]);
        $this->mouvement = StockMovement::create([
            'warehouse_id' => $this->warehouse->id,
            'type' => 'exit',
            'quantity' => 10,
            'quantity_before' => 50,
            'quantity_after' => 40,
            'reason' => 'Sortie test',
            'reference' => 'STK-TEST01',
            'created_by' => $this->agent->id,
            'status' => 'draft',
        ]);
    }

    private function createSchema(): void
    {
        foreach (['stock_movements', 'warehouses', 'users'] as $t) {
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
            $t->unsignedBigInteger('created_by')->nullable();
            $t->string('numero_transfert')->nullable();
            $t->unsignedBigInteger('entrepot_destination_id')->nullable();
            $t->text('raison_ajustement')->nullable();
            $t->text('metadata')->nullable();
            $t->unsignedBigInteger('public_request_id')->nullable();
            $t->unsignedBigInteger('request_mission_id')->nullable();
            $t->string('status')->default('draft');
            $t->unsignedBigInteger('validated_by')->nullable();
            $t->timestamp('validated_at')->nullable();
            $t->text('rejection_reason')->nullable();
            $t->timestamps();
        });
    }

    public function test_guest_redirected_to_comptable_login(): void
    {
        $this->get('/comptable/mouvements')->assertRedirect(route('comptable.login'));
    }

    public function test_non_comptable_role_denied(): void
    {
        $this->actingAs($this->agent, 'comptable')
            ->get('/comptable/mouvements')
            ->assertRedirect(route('comptable.login'));
    }

    public function test_comptable_lists_pending_movements(): void
    {
        $this->actingAs($this->comptable, 'comptable')
            ->get('/comptable/mouvements')
            ->assertOk()
            ->assertSee('STK-TEST01');
    }

    public function test_validate_marks_movement_validated(): void
    {
        $this->actingAs($this->comptable, 'comptable')
            ->post("/comptable/mouvements/{$this->mouvement->id}/valider")
            ->assertRedirect();

        $m = $this->mouvement->fresh();
        $this->assertSame('validated', $m->status);
        $this->assertSame($this->comptable->id, $m->validated_by);
        $this->assertNotNull($m->validated_at);
    }

    public function test_reject_requires_reason_and_marks_rejected(): void
    {
        $this->actingAs($this->comptable, 'comptable')
            ->post("/comptable/mouvements/{$this->mouvement->id}/rejeter", [
                'rejection_reason' => 'Quantité incohérente avec le bon de sortie.',
            ])
            ->assertRedirect();

        $m = $this->mouvement->fresh();
        $this->assertSame('rejected', $m->status);
        $this->assertNotNull($m->rejection_reason);
    }

    public function test_reject_without_reason_fails_validation(): void
    {
        $this->actingAs($this->comptable, 'comptable')
            ->from('/comptable/mouvements')
            ->post("/comptable/mouvements/{$this->mouvement->id}/rejeter", [
                'rejection_reason' => '',
            ])
            ->assertSessionHasErrors('rejection_reason');

        $this->assertSame('draft', $this->mouvement->fresh()->status);
    }

    public function test_validated_movement_cannot_be_rejected(): void
    {
        $this->mouvement->update(['status' => 'validated', 'validated_by' => $this->comptable->id]);

        $this->actingAs($this->comptable, 'comptable')
            ->post("/comptable/mouvements/{$this->mouvement->id}/rejeter", [
                'rejection_reason' => 'Tentative de rejet tardif.',
            ])
            ->assertRedirect();

        $this->assertSame('validated', $this->mouvement->fresh()->status);
    }
}
