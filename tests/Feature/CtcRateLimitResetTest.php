<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Sécurisation du reset du rate-limiter de connexion CTC.
 * La route publique GET est supprimée ; le reset passe en POST admin (2FA + throttle).
 * Schéma minimal sur SQLite :memory: — jamais la production.
 */
class CtcRateLimitResetTest extends TestCase
{
    private const LIMITER_KEY = 'ctc-login:127.0.0.1';
    private const ADMIN_RESET_URL = '/admin/ctc-login/reset-rate-limit';

    private User $admin;
    private User $ctcUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSchema();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            // 2FA déjà configurée → enforce.2fa laisse passer
            'two_factor_enabled' => true,
            'two_factor_secret' => 'TESTSECRET1234',
        ]);
        $this->ctcUser = User::factory()->create(['role' => 'ctc', 'is_active' => true]);
    }

    private function createSchema(): void
    {
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->timestamp('email_verified_at')->nullable();
            $t->string('password');
            $t->string('role')->default('citizen');
            $t->boolean('is_active')->default(true);
            $t->boolean('two_factor_enabled')->default(false);
            $t->string('two_factor_secret')->nullable();
            $t->timestamp('last_activity')->nullable();
            $t->rememberToken();
            $t->timestamps();
        });
    }

    // --- La route publique est supprimée ---

    public function test_public_reset_route_no_longer_exists(): void
    {
        $this->get('/ctc/login/reset-rate-limit')->assertStatus(404);
        $this->postJson('/ctc/login/reset-rate-limit')->assertStatus(404);
    }

    public function test_anonymous_cannot_use_admin_reset(): void
    {
        RateLimiter::hit(self::LIMITER_KEY, 300);

        $this->postJson(self::ADMIN_RESET_URL, ['ip' => '127.0.0.1'])
            ->assertStatus(401);

        $this->assertSame(1, RateLimiter::attempts(self::LIMITER_KEY));
    }

    public function test_ctc_role_cannot_use_admin_reset(): void
    {
        RateLimiter::hit(self::LIMITER_KEY, 300);

        // Connecté sur le guard admin avec un rôle non autorisé → AdminMiddleware refuse
        $this->actingAs($this->ctcUser, 'admin')
            ->postJson(self::ADMIN_RESET_URL, ['ip' => '127.0.0.1'])
            ->assertRedirect('/');

        $this->assertSame(1, RateLimiter::attempts(self::LIMITER_KEY));
    }

    // --- Reset administratif légitime ---

    public function test_admin_can_reset_limiter_for_target_ip(): void
    {
        RateLimiter::hit(self::LIMITER_KEY, 300);
        RateLimiter::hit(self::LIMITER_KEY, 300);

        $this->actingAs($this->admin, 'admin')
            ->postJson(self::ADMIN_RESET_URL, ['ip' => '127.0.0.1'])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertSame(0, RateLimiter::attempts(self::LIMITER_KEY));
    }

    public function test_admin_reset_requires_valid_ip(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->postJson(self::ADMIN_RESET_URL, ['ip' => 'not-an-ip'])
            ->assertStatus(422);
    }

    // --- Le rate-limiting continue de fonctionner après correction ---

    public function test_ctc_login_rate_limiting_still_blocks_then_admin_reset_restores(): void
    {
        // 5 tentatives échouées → limite atteinte (5 max, fenêtre 300 s)
        for ($i = 0; $i < 5; $i++) {
            $this->post('/ctc/login', [
                'email' => 'inconnu@example.sn',
                'password' => 'motdepasse-faux',
            ]);
        }

        // 6e tentative : bloquée par le limiteur (pas par les identifiants)
        $response = $this->post('/ctc/login', [
            'email' => 'inconnu@example.sn',
            'password' => 'motdepasse-faux',
        ]);
        $response->assertSessionHasErrors();
        $error = session('errors')->get('email')[0] ?? '';
        $this->assertStringContainsString('Trop de tentatives', $error);

        // Reset administratif → la limite est levée et le login re-tente les identifiants
        $this->actingAs($this->admin, 'admin')
            ->postJson(self::ADMIN_RESET_URL, ['ip' => '127.0.0.1'])
            ->assertStatus(200);

        $response = $this->post('/ctc/login', [
            'email' => 'inconnu@example.sn',
            'password' => 'motdepasse-faux',
        ]);
        $error = session('errors')->get('email')[0] ?? '';
        $this->assertStringNotContainsString('Trop de tentatives', $error);
    }
}
