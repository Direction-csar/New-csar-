<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

/**
 * Régression sécurité : les secrets 2FA et les identifiants sensibles
 * ne doivent jamais apparaître dans une sérialisation JSON du modèle
 * User (réponses API login, /api/users, etc.).
 *
 * Avant le correctif, User::$hidden ne masquait que password et
 * remember_token : two_factor_secret et two_factor_recovery_codes
 * fuitaient dans les payloads JSON.
 */
class UserSecretsHiddenTest extends TestCase
{
    public function test_sensitive_fields_are_hidden_from_serialization(): void
    {
        $user = new User();
        $user->name = 'Test';
        $user->email = 'test@example.com';
        $user->password = 'secret-hash';
        $user->remember_token = 'token-xyz';
        $user->two_factor_secret = 'JBSWY3DPEHPK3PXP';
        $user->two_factor_recovery_codes = '["code1","code2"]';

        $array = $user->toArray();
        $json = $user->toJson();

        foreach (['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'] as $field) {
            $this->assertArrayNotHasKey($field, $array, "Le champ {$field} doit être masqué.");
            $this->assertStringNotContainsString($field, $json);
        }

        $this->assertStringNotContainsString('JBSWY3DPEHPK3PXP', $json);
        $this->assertStringNotContainsString('code1', $json);
    }

    public function test_login_payload_does_not_leak_2fa_secret(): void
    {
        // Vérifie que même un user complet sérialisé pour une réponse API
        // n'expose pas les secrets — indépendamment du endpoint.
        $user = User::make([
            'name' => 'Admin',
            'email' => 'admin@csar.sn',
            'role' => 'admin',
        ]);
        $user->two_factor_secret = 'RAWSECRET123456';
        $user->two_factor_recovery_codes = '["recovery1"]';

        $payload = json_encode(['success' => true, 'user' => $user, 'token' => 'x']);

        $this->assertStringNotContainsString('RAWSECRET123456', $payload);
        $this->assertStringNotContainsString('recovery1', $payload);
    }
}
