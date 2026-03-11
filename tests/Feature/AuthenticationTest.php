<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test de la page de login admin
     */
    public function test_admin_login_page_accessible()
    {
        $response = $this->get('/admin/login');
        
        $response->assertStatus(200);
        $response->assertViewIs('auth.admin-login');
    }

    /**
     * Test de connexion admin avec identifiants valides
     */
    public function test_admin_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'admin@csar.sn',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@csar.sn',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test de connexion admin avec mot de passe invalide
     */
    public function test_admin_cannot_login_with_invalid_password()
    {
        User::factory()->create([
            'email' => 'admin@csar.sn',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@csar.sn',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /**
     * Test qu'un utilisateur non-admin ne peut pas accéder à l'interface admin
     */
    public function test_non_admin_cannot_access_admin_interface()
    {
        $user = User::factory()->create([
            'email' => 'agent@csar.sn',
            'password' => Hash::make('password123'),
            'role' => 'agent',
            'is_active' => true
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'agent@csar.sn',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /**
     * Test qu'un compte inactif ne peut pas se connecter
     */
    public function test_inactive_admin_cannot_login()
    {
        User::factory()->create([
            'email' => 'admin@csar.sn',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => false
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@csar.sn',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /**
     * Test du rate limiting (limitation de tentatives)
     */
    public function test_login_rate_limiting()
    {
        User::factory()->create([
            'email' => 'admin@csar.sn',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true
        ]);

        // Faire 5 tentatives échouées
        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', [
                'email' => 'admin@csar.sn',
                'password' => 'wrongpassword',
            ]);
        }

        // La 6ème tentative devrait être bloquée
        $response = $this->post('/admin/login', [
            'email' => 'admin@csar.sn',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors();
    }

    /**
     * Test de la déconnexion admin
     */
    public function test_admin_can_logout()
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'is_active' => true
        ]);

        $this->actingAs($user);

        $response = $this->post('/admin/logout');

        $response->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    /**
     * Test de l'accès au dashboard après connexion
     */
    public function test_admin_can_access_dashboard_after_login()
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'is_active' => true
        ]);

        $this->actingAs($user);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard.index');
    }

    /**
     * Test que le dashboard redirige vers login si non authentifié
     */
    public function test_dashboard_redirects_to_login_when_not_authenticated()
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/admin/login');
    }

    /**
     * Test de la fonction "Se souvenir de moi"
     */
    public function test_remember_me_functionality()
    {
        $user = User::factory()->create([
            'email' => 'admin@csar.sn',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@csar.sn',
            'password' => 'password123',
            'remember' => true,
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($user);
        
        // Vérifier que le cookie remember_me existe
        $this->assertNotNull(User::find($user->id)->remember_token);
    }
}











































