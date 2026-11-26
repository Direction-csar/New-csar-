<?php

namespace Tests\Feature;

use App\Models\CollectorLocation;
use App\Models\SimCollector;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Contrôle d'accès aux endpoints de géolocalisation des collecteurs.
 * Schéma minimal sur SQLite :memory: — jamais la production.
 */
class CollectorLocationAccessTest extends TestCase
{
    private SimCollector $collector;
    private User $supervisor;
    private User $admin;
    private User $citizen;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSchema();

        $this->collector = SimCollector::create([
            'name' => 'Collecteur Test',
            'status' => 'active',
        ]);
        $this->supervisor = User::factory()->create(['role' => 'supervisor', 'is_active' => true]);
        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->citizen = User::factory()->create(['role' => 'citizen', 'is_active' => true]);
    }

    private function createSchema(): void
    {
        foreach (['collector_locations', 'sim_collectors', 'users', 'personal_access_tokens'] as $t) {
            Schema::dropIfExists($t);
        }

        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->timestamp('email_verified_at')->nullable();
            $t->string('password');
            $t->string('role')->default('citizen');
            $t->boolean('is_active')->default(true);
            $t->rememberToken();
            $t->timestamps();
        });

        Schema::create('sim_collectors', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->nullable();
            $t->string('phone')->nullable();
            $t->string('password_hash')->nullable();
            $t->json('assigned_zones')->nullable();
            $t->string('status')->default('active');
            $t->timestamp('last_sync')->nullable();
            $t->integer('total_collections')->default(0);
            $t->timestamps();
        });

        Schema::create('collector_locations', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('collector_id');
            $t->decimal('latitude', 10, 8);
            $t->decimal('longitude', 11, 8);
            $t->decimal('accuracy', 8, 2)->nullable();
            $t->string('status')->default('active');
            $t->string('current_market')->nullable();
            $t->integer('collections_today')->default(0);
            $t->timestamp('last_activity_at')->nullable();
            $t->json('metadata')->nullable();
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
    }

    // --- Accès refusés ---

    public function test_anonymous_cannot_list_locations(): void
    {
        $this->getJson('/api/mobile/collectors/locations')->assertStatus(401);
    }

    public function test_anonymous_cannot_post_location(): void
    {
        $this->postJson('/api/mobile/collectors/location', [
            'collector_id' => $this->collector->id,
            'latitude' => 14.7,
            'longitude' => -17.4,
        ])->assertStatus(401);

        $this->assertDatabaseCount('collector_locations', 0);
    }

    public function test_citizen_session_cannot_list_locations(): void
    {
        $this->actingAs($this->citizen, 'web')
            ->getJson('/api/mobile/collectors/locations')
            ->assertStatus(401);
    }

    // --- Accès staff légitimes ---

    public function test_supervisor_session_can_list_locations(): void
    {
        CollectorLocation::create([
            'collector_id' => $this->collector->id,
            'latitude' => 14.7,
            'longitude' => -17.4,
            'status' => 'active',
            'last_activity_at' => now(),
        ]);

        $response = $this->actingAs($this->supervisor, 'supervisor')
            ->getJson('/api/mobile/collectors/locations');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.0.collector_id', $this->collector->id);
    }

    public function test_admin_session_can_list_locations(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->getJson('/api/mobile/collectors/locations')
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_staff_session_can_post_location_for_collector(): void
    {
        $this->actingAs($this->supervisor, 'supervisor')
            ->postJson('/api/mobile/collectors/location', [
                'collector_id' => $this->collector->id,
                'latitude' => 14.71,
                'longitude' => -17.45,
            ])->assertStatus(200)->assertJson(['success' => true]);

        $this->assertDatabaseHas('collector_locations', [
            'collector_id' => $this->collector->id,
        ]);
    }

    // --- Token Sanctum collecteur ---

    public function test_collector_token_can_post_own_location(): void
    {
        $token = $this->collector->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/mobile/collectors/location', [
                'latitude' => 14.72,
                'longitude' => -17.46,
                'status' => 'collecting',
            ])->assertStatus(200)->assertJson(['success' => true]);

        $this->assertDatabaseHas('collector_locations', [
            'collector_id' => $this->collector->id,
            'status' => 'collecting',
        ]);
    }

    public function test_collector_token_cannot_spoof_another_collector(): void
    {
        $other = SimCollector::create(['name' => 'Autre collecteur', 'status' => 'active']);
        $token = $this->collector->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/mobile/collectors/location', [
                'collector_id' => $other->id,
                'latitude' => 14.7,
                'longitude' => -17.4,
            ])->assertStatus(403);

        $this->assertDatabaseCount('collector_locations', 0);
    }

    public function test_staff_post_without_collector_id_is_rejected(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->postJson('/api/mobile/collectors/location', [
                'latitude' => 14.7,
                'longitude' => -17.4,
            ])->assertStatus(401);
    }
}
