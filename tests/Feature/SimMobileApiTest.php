<?php

namespace Tests\Feature;

use App\Models\SimCollector;
use App\Models\SimMarket;
use App\Models\SimMobileCollection;
use App\Models\SimProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * API mobile « CSAR Collecte SIM » (/mobile/v1) : authentification,
 * jeton Sanctum, cloisonnement par marchés assignés, collectes,
 * synchronisation et historique. Base SQLite en mémoire uniquement.
 */
class SimMobileApiTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'Test-Collecte#2026';

    private function makeMarket(string $name): SimMarket
    {
        $regionId = DB::table('sim_regions')->insertGetId([
            'name' => 'Région ' . $name, 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $departmentId = DB::table('sim_departments')->insertGetId([
            'sim_region_id' => $regionId, 'name' => 'Département ' . $name, 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return SimMarket::create([
            'sim_department_id' => $departmentId,
            'name' => $name,
            'slug' => Str::slug($name),
            'market_type' => 'urbain',
            'is_active' => true,
        ]);
    }

    private function makeProduct(): SimProduct
    {
        $categoryId = DB::table('sim_product_categories')->insertGetId([
            'name' => 'Céréales', 'slug' => 'cereales-' . Str::random(4), 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return SimProduct::create([
            'sim_product_category_id' => $categoryId,
            'name' => 'Riz brisé',
            'slug' => 'riz-brise-' . Str::random(4),
            'unit' => 'kg',
            'is_active' => true,
        ]);
    }

    private function makeCollector(array $zones, string $status = 'active'): SimCollector
    {
        return SimCollector::create([
            'name' => 'Collecteur Test',
            'email' => 'collecteur.' . Str::random(6) . '@test.local',
            'phone' => '770000000',
            'password_hash' => password_hash(self::PASSWORD, PASSWORD_BCRYPT),
            'status' => $status,
            'assigned_zones' => $zones,
        ]);
    }

    private function loginToken(SimCollector $collector): string
    {
        return $this->postJson('/mobile/v1/login', [
            'email' => $collector->email,
            'password' => self::PASSWORD,
        ])->assertOk()->json('data.token');
    }

    private function collectionPayload(SimMarket $market, SimProduct $product): array
    {
        return [
            'market_id' => $market->id,
            'product_id' => $product->id,
            'retail_price' => 450,
            'collection_date' => now()->toDateString(),
        ];
    }

    public function test_login_returns_sanctum_token_without_password_hash(): void
    {
        $market = $this->makeMarket('Marché Kermel');
        $collector = $this->makeCollector([$market->id]);

        $response = $this->postJson('/mobile/v1/login', [
            'email' => $collector->email,
            'password' => self::PASSWORD,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.collector.email', $collector->email)
            ->assertJsonMissingPath('data.collector.password_hash');
        $this->assertNotEmpty($response->json('data.token'));
        $this->assertSame(1, $collector->tokens()->count());
    }

    public function test_login_rejects_wrong_password_and_inactive_account(): void
    {
        $active = $this->makeCollector([1]);
        $suspended = $this->makeCollector([1], 'suspended');

        $this->postJson('/mobile/v1/login', ['email' => $active->email, 'password' => 'mauvais-mdp'])
            ->assertStatus(401);
        $this->postJson('/mobile/v1/login', ['email' => $suspended->email, 'password' => self::PASSWORD])
            ->assertStatus(403);
        $this->assertSame(0, $suspended->tokens()->count());
    }

    public function test_protected_endpoints_require_token(): void
    {
        $this->getJson('/mobile/v1/markets')->assertStatus(401);
        $this->getJson('/mobile/v1/profile')->assertStatus(401);
        $this->postJson('/mobile/v1/collections', [])->assertStatus(401);
    }

    public function test_markets_list_only_assigned_markets(): void
    {
        $assigned = $this->makeMarket('Marché Assigné');
        $other = $this->makeMarket('Marché Autre');
        $collector = $this->makeCollector([$assigned->id]);

        $response = $this->withToken($this->loginToken($collector))->getJson('/mobile/v1/markets');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertSame([$assigned->id], $ids);
        $this->assertNotContains($other->id, $ids);
    }

    public function test_collector_without_markets_is_blocked(): void
    {
        $market = $this->makeMarket('Marché Kermel');
        $product = $this->makeProduct();
        $collector = $this->makeCollector([]);
        $token = $this->loginToken($collector);

        $this->withToken($token)->getJson('/mobile/v1/markets')->assertStatus(403);
        $this->withToken($token)->postJson('/mobile/v1/collections', $this->collectionPayload($market, $product))
            ->assertStatus(403);
        $this->assertSame(0, SimMobileCollection::count());
    }

    public function test_collection_on_assigned_market_is_recorded(): void
    {
        $market = $this->makeMarket('Marché Kermel');
        $product = $this->makeProduct();
        $collector = $this->makeCollector([$market->id]);

        $this->withToken($this->loginToken($collector))
            ->postJson('/mobile/v1/collections', $this->collectionPayload($market, $product))
            ->assertOk()
            ->assertJsonPath('data.sync_status', 'pending');

        $this->assertDatabaseHas('sim_mobile_collections', [
            'collector_id' => $collector->id,
            'market_id' => $market->id,
        ]);
        $this->assertSame(1, $collector->fresh()->total_collections);
    }

    public function test_collection_on_unassigned_market_is_refused(): void
    {
        $assigned = $this->makeMarket('Marché Assigné');
        $other = $this->makeMarket('Marché Autre');
        $product = $this->makeProduct();
        $collector = $this->makeCollector([$assigned->id]);

        $this->withToken($this->loginToken($collector))
            ->postJson('/mobile/v1/collections', $this->collectionPayload($other, $product))
            ->assertStatus(403);

        $this->assertSame(0, SimMobileCollection::count());
    }

    public function test_history_contains_only_own_collections(): void
    {
        $market = $this->makeMarket('Marché Kermel');
        $product = $this->makeProduct();
        $me = $this->makeCollector([$market->id]);
        $other = $this->makeCollector([$market->id]);

        $this->withToken($this->loginToken($other))
            ->postJson('/mobile/v1/collections', $this->collectionPayload($market, $product))->assertOk();
        $this->app['auth']->forgetGuards();

        $myToken = $this->loginToken($me);
        $this->withToken($myToken)
            ->postJson('/mobile/v1/collections', $this->collectionPayload($market, $product))->assertOk();

        $history = $this->withToken($myToken)->getJson('/mobile/v1/collections')->assertOk()->json('data');
        $this->assertCount(1, $history);

        $this->withToken($myToken)->getJson('/mobile/v1/stats')
            ->assertOk()
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.pending_sync', 1);
    }

    public function test_sync_marks_pending_collections_and_logs_history(): void
    {
        $market = $this->makeMarket('Marché Kermel');
        $product = $this->makeProduct();
        $collector = $this->makeCollector([$market->id]);
        $token = $this->loginToken($collector);

        $this->withToken($token)
            ->postJson('/mobile/v1/collections', $this->collectionPayload($market, $product))->assertOk();

        $this->withToken($token)->postJson('/mobile/v1/sync')
            ->assertOk()
            ->assertJsonPath('data.synced_count', 1);

        $this->assertSame(0, SimMobileCollection::where('sync_status', 'pending')->count());
        $this->withToken($token)->getJson('/mobile/v1/sync/history')
            ->assertOk()
            ->assertJsonPath('data.data.0.data_count', 1);
    }

    public function test_logout_revokes_current_token(): void
    {
        $collector = $this->makeCollector([1]);
        $token = $this->loginToken($collector);

        $this->withToken($token)->postJson('/mobile/v1/logout')->assertOk();

        $this->assertSame(0, $collector->tokens()->count());
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/mobile/v1/profile')->assertStatus(401);
    }

    public function test_suspended_collector_loses_access_with_existing_token(): void
    {
        $market = $this->makeMarket('Marché Kermel');
        $collector = $this->makeCollector([$market->id]);
        $token = $this->loginToken($collector);

        $collector->update(['status' => 'suspended']);
        $this->app['auth']->forgetGuards();

        $this->withToken($token)->getJson('/mobile/v1/markets')->assertStatus(403);
    }

    public function test_staff_user_token_cannot_use_collector_endpoints(): void
    {
        $user = User::factory()->create(['role' => 'comptable', 'is_active' => true]);
        $token = $user->createToken('comptable-mobile')->plainTextToken;

        $this->withToken($token)->getJson('/mobile/v1/profile')->assertStatus(403);
        $this->withToken($token)->getJson('/mobile/v1/markets')->assertStatus(403);
    }

    public function test_admin_can_create_collector_who_can_then_log_in(): void
    {
        $market = $this->makeMarket('Marché Kermel');
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->two_factor_enabled = true;
        $admin->two_factor_secret = 'JBSWY3DPEHPK3PXP';
        $admin->save();

        $this->actingAs($admin, 'admin')->post('/admin/sim/collectors', [
            'name' => 'Nouveau Collecteur',
            'email' => 'nouveau.collecteur@test.local',
            'phone' => '771112233',
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
            'status' => 'active',
            'assigned_markets' => [$market->id],
        ])->assertRedirect(route('admin.sim.collectors'));

        $created = SimCollector::where('email', 'nouveau.collecteur@test.local')->firstOrFail();
        $this->assertNotSame(self::PASSWORD, $created->password_hash);
        $this->assertSame([$market->id], array_map('intval', $created->assigned_zones));

        $this->app['auth']->forgetGuards();
        $this->postJson('/mobile/v1/login', [
            'email' => 'nouveau.collecteur@test.local',
            'password' => self::PASSWORD,
        ])->assertOk()->assertJsonPath('success', true);
    }
}
