<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Tests RGPD — Droits utilisateurs (Articles 15, 17, 20 RGPD).
 */
class GdprTest extends TestCase
{
    use RefreshDatabase;

    private function authedUser(array $attrs = []): User
    {
        $user = User::factory()->create(array_merge([
            'email'     => 'citoyen@example.test',
            'password'  => Hash::make('Secret#2024'),
            'role'      => 'user',
            'is_active' => true,
        ], $attrs));

        $this->actingAs($user);

        return $user;
    }

    private string $base = '/fr/mes-donnees';

    public function test_gdpr_page_requires_authentication(): void
    {
        $response = $this->get($this->base);
        $response->assertStatus(302);
    }

    public function test_authenticated_user_can_view_gdpr_page(): void
    {
        $this->authedUser();

        $response = $this->get($this->base);

        $response->assertStatus(200);
        $response->assertViewIs('public.gdpr.index');
    }

    public function test_user_can_export_personal_data_as_json(): void
    {
        $user = $this->authedUser(['name' => 'Awa Diop']);

        $response = $this->post($this->base . '/exporter');

        $response->assertStatus(200);
        $this->assertStringContainsString('application/json', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('attachment', (string) $response->headers->get('content-disposition'));

        $payload = json_decode($response->getContent(), true);
        $this->assertIsArray($payload);
        $this->assertEquals($user->email, $payload['user_profile']['email'] ?? null);
        $this->assertArrayHasKey('export_date', $payload);
    }

    public function test_account_deletion_requires_correct_password(): void
    {
        $this->authedUser();

        $response = $this->delete($this->base . '/supprimer-compte', [
            'password'     => 'WRONG-PASSWORD',
            'confirmation' => 'SUPPRIMER MON COMPTE',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_account_deletion_requires_typed_confirmation(): void
    {
        $this->authedUser();

        $response = $this->delete($this->base . '/supprimer-compte', [
            'password'     => 'Secret#2024',
            'confirmation' => 'oui',
        ]);

        $response->assertSessionHasErrors('confirmation');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_admin_account_cannot_be_self_deleted_via_gdpr(): void
    {
        $this->authedUser(['role' => 'admin']);

        $response = $this->delete($this->base . '/supprimer-compte', [
            'password'     => 'Secret#2024',
            'confirmation' => 'SUPPRIMER MON COMPTE',
        ]);

        $response->assertSessionHasErrors('confirmation');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_user_can_delete_own_account_with_password_and_confirmation(): void
    {
        $user = $this->authedUser();

        $response = $this->delete($this->base . '/supprimer-compte', [
            'password'     => 'Secret#2024',
            'confirmation' => 'SUPPRIMER MON COMPTE',
        ]);

        $response->assertRedirect('/');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
