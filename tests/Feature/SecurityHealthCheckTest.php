<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * Vérifie que la commande Artisan security:healthcheck s'exécute sans erreur
 * et produit le rapport attendu.
 */
class SecurityHealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_healthcheck_command_runs_successfully(): void
    {
        $exitCode = Artisan::call('security:healthcheck');

        $this->assertContains($exitCode, [0, 1], 'La commande doit retourner 0 (OK) ou 1 (warnings).');

        $output = Artisan::output();

        $this->assertStringContainsString('RAPPORT DE SÉCURITÉ CSAR', $output);
        $this->assertStringContainsString('app_debug', $output);
        $this->assertStringContainsString('app_env', $output);
    }

    public function test_security_healthcheck_supports_json_output(): void
    {
        $exitCode = Artisan::call('security:healthcheck', ['--json' => true]);

        $this->assertContains($exitCode, [0, 1]);

        $output = trim(Artisan::output());
        $payload = json_decode($output, true);

        $this->assertIsArray($payload, 'La sortie --json doit être un JSON valide.');
        $this->assertArrayHasKey('checks', $payload);
        $this->assertArrayHasKey('errors', $payload);
        $this->assertArrayHasKey('warnings', $payload);
    }
}
