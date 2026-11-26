<?php

namespace Tests\Feature;

use App\Models\SimCollector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * API « Comptable des matières » exécutée sur le schéma réel des
 * migrations (et non un schéma reconstruit à la main) : vérifie que la
 * connexion, le jeton Sanctum et les endpoints fonctionnent réellement.
 */
class ComptableApiRealSchemaTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'Test-Comptable#2026';

    private function makeUser(string $role, bool $active = true): User
    {
        return User::factory()->create([
            'role' => $role,
            'is_active' => $active,
            'password' => bcrypt(self::PASSWORD),
        ]);
    }

    private function login(User $user)
    {
        return $this->postJson('/api/comptable/v1/login', [
            'email' => $user->email,
            'password' => self::PASSWORD,
        ]);
    }

    public function test_comptable_logs_in_and_reaches_every_read_endpoint(): void
    {
        $comptable = $this->makeUser('comptable');

        $token = $this->login($comptable)
            ->assertOk()
            ->assertJsonPath('user.role', 'comptable')
            ->assertJsonMissingPath('user.password')
            ->json('token');
        $this->assertNotEmpty($token);

        foreach (['dashboard', 'movements', 'warehouses', 'fuel/summary', 'equipment-assignments'] as $endpoint) {
            $this->withToken($token)->getJson('/api/comptable/v1/' . $endpoint)
                ->assertOk();
        }
    }

    public function test_non_comptable_and_inactive_accounts_are_refused_at_login(): void
    {
        $this->login($this->makeUser('magasinier'))->assertStatus(403);
        $this->login($this->makeUser('comptable', false))->assertStatus(403);
    }

    public function test_deactivated_comptable_loses_access_with_existing_token(): void
    {
        $comptable = $this->makeUser('comptable');
        $token = $this->login($comptable)->assertOk()->json('token');

        $comptable->update(['is_active' => false]);
        $this->app['auth']->forgetGuards();

        $this->withToken($token)->getJson('/api/comptable/v1/movements')->assertStatus(403);
    }

    public function test_sim_collector_token_cannot_use_comptable_api(): void
    {
        $collector = SimCollector::create([
            'name' => 'Collecteur', 'email' => 'c@test.local', 'phone' => '770000000', 'status' => 'active',
            'password_hash' => password_hash(self::PASSWORD, PASSWORD_BCRYPT), 'assigned_zones' => [],
        ]);
        $token = $collector->createToken('mobile-app')->plainTextToken;

        $this->withToken($token)->getJson('/api/comptable/v1/movements')->assertStatus(403);
        $this->withToken($token)->postJson('/api/comptable/v1/movements/1/validate')->assertStatus(403);
    }
}
