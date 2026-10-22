<?php

namespace Tests\Feature;

use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Tests de l'API mobile « CSAR Comptable des matières » :
 * authentification Sanctum, liste/détail des mouvements,
 * décisions validate/reject — mêmes règles que le portail web.
 */
class ComptableApiTest extends TestCase
{
    protected User $comptable;
    protected User $magasinier;
    protected Warehouse $warehouse;
    protected StockMovement $mouvement;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSchema();

        $this->comptable = $this->makeUser('comptable');
        $this->magasinier = $this->makeUser('magasinier');
        $this->warehouse = Warehouse::create([
            'name' => 'Magasin API',
            'capacity' => 1000,
            'current_stock' => 100,
            'status' => 'active',
            'is_active' => true,
        ]);
        $this->mouvement = StockMovement::create([
            'warehouse_id' => $this->warehouse->id,
            'type' => 'exit',
            'quantity' => 10,
            'quantity_before' => 100,
            'quantity_after' => 90,
            'reason' => 'Sortie pour mission DTL',
            'reference' => 'STK-API01',
            'created_by' => $this->magasinier->id,
            'status' => 'draft',
        ]);
    }

    private function makeUser(string $role): User
    {
        $user = User::create([
            'name' => 'API ' . $role . ' ' . substr(md5(uniqid()), 0, 4),
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
        foreach (['personal_access_tokens', 'stock_movements', 'warehouses', 'users'] as $t) {
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
            $t->unsignedBigInteger('stock_id')->nullable();
            $t->unsignedBigInteger('warehouse_id');
            $t->string('type');
            $t->decimal('quantity', 15, 2)->default(0);
            $t->decimal('quantity_before', 15, 2)->nullable();
            $t->decimal('quantity_after', 15, 2)->nullable();
            $t->string('reason')->nullable();
            $t->string('reference')->nullable();
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
    }

    /** @test Le login émet un jeton Sanctum pour un comptable actif */
    public function test_login_issues_token_for_comptable(): void
    {
        $resp = $this->postJson('/api/comptable/v1/login', [
            'email' => $this->comptable->email,
            'password' => 'password123',
        ]);

        $resp->assertOk()->assertJsonStructure(['token', 'user' => ['id', 'role']]);
        $this->assertSame('comptable', $resp->json('user.role'));
    }

    /** @test Le login refuse un autre rôle même avec bons identifiants */
    public function test_login_denies_non_comptable(): void
    {
        $this->postJson('/api/comptable/v1/login', [
            'email' => $this->magasinier->email,
            'password' => 'password123',
        ])->assertForbidden();
    }

    /** @test Les endpoints exigent un jeton */
    public function test_endpoints_require_authentication(): void
    {
        $this->getJson('/api/comptable/v1/movements')->assertUnauthorized();
        $this->postJson("/api/comptable/v1/movements/{$this->mouvement->id}/validate")->assertUnauthorized();
    }

    /** @test Un jeton non comptable est refusé sur chaque endpoint */
    public function test_non_comptable_token_denied(): void
    {
        Sanctum::actingAs($this->magasinier, ['comptable']);

        $this->getJson('/api/comptable/v1/movements')->assertForbidden();
        $this->postJson("/api/comptable/v1/movements/{$this->mouvement->id}/validate")->assertForbidden();
    }

    /** @test La liste filtre par statut et renvoie le mouvement */
    public function test_movements_list_filtered(): void
    {
        Sanctum::actingAs($this->comptable, ['comptable']);

        $this->getJson('/api/comptable/v1/movements?status=draft&q=STK-API01')
            ->assertOk()
            ->assertJsonPath('data.0.reference', 'STK-API01');
    }

    /** @test Validation comptable via API : statut + validateur enregistrés */
    public function test_validate_movement_via_api(): void
    {
        Sanctum::actingAs($this->comptable, ['comptable']);

        $this->postJson("/api/comptable/v1/movements/{$this->mouvement->id}/validate")
            ->assertOk()
            ->assertJsonPath('movement.status', 'validated');

        $m = $this->mouvement->fresh();
        $this->assertSame($this->comptable->id, $m->validated_by);
        $this->assertNotNull($m->validated_at);
    }

    /** @test Un mouvement validé renvoie 422 à la revalidation */
    public function test_validated_movement_cannot_be_revalidated(): void
    {
        $this->mouvement->update(['status' => 'validated']);
        Sanctum::actingAs($this->comptable, ['comptable']);

        $this->postJson("/api/comptable/v1/movements/{$this->mouvement->id}/validate")
            ->assertStatus(422);
    }

    /** @test Le rejet exige un motif ≥ 10 caractères */
    public function test_reject_requires_reason(): void
    {
        Sanctum::actingAs($this->comptable, ['comptable']);

        $this->postJson("/api/comptable/v1/movements/{$this->mouvement->id}/reject", [
            'rejection_reason' => 'court',
        ])->assertStatus(422);
    }

    /** @test Rejet complet : statut rejected + motif tracé */
    public function test_reject_movement_via_api(): void
    {
        Sanctum::actingAs($this->comptable, ['comptable']);

        $this->postJson("/api/comptable/v1/movements/{$this->mouvement->id}/reject", [
            'rejection_reason' => 'Quantité incohérente avec le bon de sortie.',
        ])->assertOk()->assertJsonPath('movement.status', 'rejected');

        $this->assertSame('Quantité incohérente avec le bon de sortie.', $this->mouvement->fresh()->rejection_reason);
    }

    /** @test Le tableau de bord renvoie stats, alertes et mouvements récents */
    public function test_dashboard_payload(): void
    {
        Sanctum::actingAs($this->comptable, ['comptable']);

        $this->getJson('/api/comptable/v1/dashboard')
            ->assertOk()
            ->assertJsonStructure(['stats' => ['draft', 'validated', 'rejected'], 'low_stock_warehouses', 'recent_movements']);
    }
}
