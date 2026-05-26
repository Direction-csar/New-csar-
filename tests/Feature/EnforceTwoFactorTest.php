<?php

namespace Tests\Feature;

use App\Http\Middleware\EnforceTwoFactor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Tests du middleware enforce.2fa : force l'activation 2FA des rôles sensibles.
 */
class EnforceTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Route de test isolée (pas besoin de toute l'app)
        Route::middleware(['web', 'auth', 'enforce.2fa:web'])
            ->get('/__test/protected', fn () => 'OK')
            ->name('test.protected');

        Route::middleware(['web'])
            ->get('/web/2fa/setup', fn () => '2FA setup')
            ->name('web.2fa.setup');
    }

    public function test_user_without_2fa_is_redirected_to_setup(): void
    {
        $user = User::factory()->create([
            'role'                => 'admin',
            'two_factor_enabled'  => false,
            'two_factor_secret'   => null,
        ]);

        $this->actingAs($user);

        $response = $this->get('/__test/protected');

        $response->assertRedirect(route('web.2fa.setup'));
    }

    public function test_user_with_2fa_enabled_can_access_protected_route(): void
    {
        $user = User::factory()->create([
            'role'                => 'admin',
            'two_factor_enabled'  => true,
            'two_factor_secret'   => 'SOMEFAKESECRET',
        ]);

        $this->actingAs($user);

        $response = $this->get('/__test/protected');

        $response->assertStatus(200);
        $response->assertSee('OK');
    }

    public function test_middleware_class_bypasses_when_no_user(): void
    {
        $middleware = new EnforceTwoFactor();

        $request = Request::create('/test', 'GET');
        $response = $middleware->handle($request, fn ($r) => response('passed'), 'admin');

        $this->assertEquals('passed', $response->getContent());
    }
}
