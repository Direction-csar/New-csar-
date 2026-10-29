<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Reproduction du parcours "créer un utilisateur Comptable des matières"
 * depuis l'espace /admin, puis connexion du compte sur le portail Web
 * /comptable et sur l'API mobile csar_comptable.
 * Schéma minimal SQLite :memory:.
 */
class AdminUserCreationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createSchema();
    }

    private function makeUser(string $role, array $attrs = []): User
    {
        $user = User::create(array_merge([
            'name' => 'User ' . $role,
            'email' => $role . substr(md5(uniqid()), 0, 6) . '@csar.test',
            'password' => Hash::make('password123'),
            'phone' => '770000000',
            'is_active' => true,
        ], $attrs));
        $user->role = $role;
        $user->save();
        return $user->refresh();
    }

    private function createSchema(): void
    {
        foreach (['personal_access_tokens', 'notifications', 'public_requests', 'stock_movements', 'equipment_assignments', 'stocks', 'warehouses', 'users', 'roles'] as $t) {
            Schema::dropIfExists($t);
        }

        // Table roles : mêmes identifiants qu'en base de développement.
        Schema::create('roles', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->timestamps();
        });
        \DB::table('roles')->insert([
            ['id' => 1, 'name' => 'ctc'],
            ['id' => 2, 'name' => 'superviseur'],
            ['id' => 3, 'name' => 'drh'],
            ['id' => 4, 'name' => 'cpm'],
            ['id' => 5, 'name' => 'dpse'],
            ['id' => 6, 'name' => 'dtl'],
            ['id' => 7, 'name' => 'inspecteur'],
            ['id' => 8, 'name' => 'magasinier'],
            ['id' => 9, 'name' => 'comptable'],
            ['id' => 10, 'name' => 'admin'],
            ['id' => 11, 'name' => 'dg'],
            ['id' => 12, 'name' => 'responsable'],
            ['id' => 13, 'name' => 'agent'],
        ]);

        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->timestamp('email_verified_at')->nullable();
            $t->string('password')->nullable();
            $t->unsignedInteger('role_id')->nullable();
            $t->string('phone')->nullable();
            $t->string('position')->nullable();
            $t->string('department')->nullable();
            $t->text('two_factor_secret')->nullable();
            $t->boolean('two_factor_enabled')->default(false);
            $t->timestamp('two_factor_verified_at')->nullable();
            $t->text('two_factor_recovery_codes')->nullable();
            $t->string('address')->nullable();
            $t->string('avatar')->nullable();
            $t->timestamp('last_login_at')->nullable();
            $t->unsignedBigInteger('warehouse_id')->nullable();
            $t->string('role')->default('agent');
            $t->boolean('is_active')->default(true);
            $t->string('status')->nullable();
            $t->string('google_id')->nullable();
            $t->timestamp('last_activity')->nullable();
            $t->rememberToken();
            $t->timestamps();
        });

        Schema::create('warehouses', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('region')->nullable();
            $t->string('city')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        // Le layout admin interroge public_requests (compteurs workflow sidebar).
        Schema::create('public_requests', function (Blueprint $t) {
            $t->id();
            $t->string('workflow_status')->nullable();
            $t->timestamps();
        });

        // Notification::create dans UserController@store.
        Schema::create('notifications', function (Blueprint $t) {
            $t->id();
            $t->string('type')->nullable();
            $t->string('title')->nullable();
            $t->text('message')->nullable();
            $t->text('data')->nullable();
            $t->boolean('read')->default(false);
            $t->unsignedBigInteger('user_id')->nullable();
            $t->timestamp('read_at')->nullable();
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

        // Tables consultées par le tableau de bord Comptable.
        Schema::create('stocks', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('warehouse_id')->nullable();
            $t->string('item_name');
            $t->decimal('quantity', 15, 2)->default(0);
            $t->decimal('min_quantity', 15, 2)->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('stock_movements', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('warehouse_id');
            $t->string('type');
            $t->decimal('quantity', 15, 2)->default(0);
            $t->string('reference')->nullable();
            $t->string('produit')->nullable();
            $t->string('unite')->nullable();
            $t->string('status')->default('draft');
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
        });

        Schema::create('equipment_assignments', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('warehouse_id')->nullable();
            $t->string('produit');
            $t->string('agent_name');
            $t->string('status')->default('affectee');
            $t->unsignedBigInteger('assigned_by');
            $t->timestamps();
        });
    }

    private function adminWith2fa(): User
    {
        // Un admin « prêt » : 2FA activée (EnforceTwoFactor le laisse passer).
        return $this->makeUser('admin', [
            'two_factor_enabled' => true,
            'two_factor_secret' => encrypt('JBSWY3DPEHPK3PXP'),
        ]);
    }

    // ===== Diagnostic : flux login et redirections admin =====

    public function test_admin_login_without_2fa_redirects_to_dashboard(): void
    {
        $this->makeUser('admin');
        $admin = User::where('role', 'admin')->first();

        $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'password123',
        ])->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_login_with_2fa_redirects_to_challenge(): void
    {
        $this->adminWith2fa();
        $admin = User::where('role', 'admin')->first();

        $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'password123',
        ])->assertRedirect(route('admin.2fa.challenge'));
    }

    public function test_admin_without_2fa_is_redirected_to_setup(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin, 'admin')
            ->get('/admin')
            ->assertRedirect(route('admin.2fa.setup'));
    }

    public function test_admin_with_2fa_reaches_users_index(): void
    {
        $admin = $this->adminWith2fa();

        $this->actingAs($admin, 'admin')
            ->get('/admin/users')
            ->assertOk();
    }

    public function test_admin_with_2fa_reaches_user_create_form(): void
    {
        $admin = $this->adminWith2fa();

        $response = $this->actingAs($admin, 'admin')->get('/admin/users/create');

        $response->assertOk();
        $response->assertSee('Comptable des matières', false);
    }

    // ===== Création du compte Comptable =====

    public function test_admin_can_create_comptable_user(): void
    {
        $admin = $this->adminWith2fa();

        $response = $this->actingAs($admin, 'admin')->post('/admin/users', [
            'name' => 'Comptable Matieres',
            'email' => 'comptable.test@csar.test',
            'phone' => '771234567',
            'role' => 'comptable',
            'status' => 'actif',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $comptable = User::where('email', 'comptable.test@csar.test')->first();
        $this->assertNotNull($comptable);
        $this->assertSame('comptable', $comptable->role);
        $this->assertSame(9, $comptable->role_id);
        $this->assertTrue((bool) $comptable->is_active);
        $this->assertTrue(Hash::check('Secret123!', $comptable->password));
    }

    public function test_admin_can_create_user_with_responsable_role(): void
    {
        $admin = $this->adminWith2fa();

        $response = $this->actingAs($admin, 'admin')->post('/admin/users', [
            'name' => 'Responsable Test',
            'email' => 'responsable.test@csar.test',
            'phone' => '772345678',
            'role' => 'responsable',
            'status' => 'actif',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHasNoErrors();

        $user = User::where('email', 'responsable.test@csar.test')->first();
        $this->assertNotNull($user);
        $this->assertSame('responsable', $user->role);
        $this->assertSame(12, $user->role_id);
    }

    // ===== Connexion Web /comptable =====

    public function test_comptable_web_login_lands_on_portal(): void
    {
        $comptable = $this->makeUser('comptable');

        $response = $this->post('/comptable/login', [
            'email' => $comptable->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('comptable.dashboard'));
        $this->assertTrue(\Illuminate\Support\Facades\Auth::guard('comptable')->check());
    }

    public function test_comptable_dashboard_loads(): void
    {
        $comptable = $this->makeUser('comptable');

        $this->actingAs($comptable, 'comptable')
            ->get('/comptable')
            ->assertOk();
    }

    // ===== Connexion API csar_comptable =====

    public function test_comptable_api_login_returns_token(): void
    {
        $comptable = $this->makeUser('comptable');

        $response = $this->postJson('/api/comptable/v1/login', [
            'email' => $comptable->email,
            'password' => 'password123',
        ]);

        $response->assertOk()->assertJsonStructure(['token', 'user' => ['id', 'role']]);
        $this->assertSame('comptable', $response->json('user.role'));
    }

    public function test_api_login_rejects_non_comptable(): void
    {
        $agent = $this->makeUser('agent');

        $this->postJson('/api/comptable/v1/login', [
            'email' => $agent->email,
            'password' => 'password123',
        ])->assertForbidden();
    }
}
