<?php

namespace Tests\Feature;

use App\Models\PublicRequest;
use App\Models\User;
use App\Services\DgDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Tableau de bord exécutif DG : données réelles, filtres, pièces
 * déposées consultables et garde-fous d'accès.
 */
class DgDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role, bool $twoFactor = true): User
    {
        $user = User::create([
            'name' => 'Test ' . $role . ' ' . substr(md5(uniqid()), 0, 4),
            'email' => $role . substr(md5(uniqid()), 0, 6) . '@csar.test',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $user->role = $role;
        if ($twoFactor) {
            $user->two_factor_enabled = true;
            $user->two_factor_secret = 'JBSWY3DPEHPK3PXP';
        }
        $user->save();

        return $user->refresh();
    }

    private function makeRequest(string $workflowStatus, array $overrides = []): PublicRequest
    {
        return PublicRequest::create(array_merge([
            'tracking_code' => 'CSAR-DG' . strtoupper(substr(md5(uniqid()), 0, 8)),
            'type' => 'aide_alimentaire',
            'status' => 'pending',
            'workflow_status' => $workflowStatus,
            'full_name' => 'Demandeur Test',
            'phone' => '770000000',
            'email' => 'demandeur@test.sn',
            'region' => 'Dakar',
            'address' => 'Rue de test, Dakar',
            'description' => 'Description de test pour le scénario DG.',
            'request_date' => now()->toDateString(),
            'subject' => 'Objet de test DG ' . substr(md5(uniqid()), 0, 6),
            'urgency' => 'haute',
        ], $overrides));
    }

    private function actingAsDg(): User
    {
        $dg = $this->makeUser('dg');
        $this->actingAs($dg, 'dg');

        return $dg;
    }

    public function test_dashboard_renders_with_real_sections_and_no_fake_data(): void
    {
        $this->actingAsDg();
        $demande = $this->makeRequest('scannee', ['full_name' => 'Demandeur Unique']);

        $response = $this->get(route('dg.dashboard'));

        $response->assertOk()
            ->assertSee('Dossiers en attente de votre décision')
            ->assertSee('Évolution des demandes')
            ->assertSee('Journal du workflow')
            ->assertSee('Alertes de stock')
            ->assertSee($demande->tracking_code)
            ->assertSee('Demandeur Unique')
            // Aucune donnée factice historique ne doit subsister.
            ->assertDontSee('Entrepôt Principal CSAR')
            ->assertDontSee('Système opérationnel');
    }

    public function test_dashboard_filters_are_accepted_without_error(): void
    {
        $this->actingAsDg();
        $this->makeRequest('scannee', ['region' => 'Dakar']);
        $this->makeRequest('soumise', ['region' => 'Thiès', 'type' => 'aide_urgence']);

        $this->get(route('dg.dashboard', [
            'period' => 'year',
            'region' => 'Dakar',
            'type' => 'aide_alimentaire',
        ]))->assertOk();
    }

    public function test_service_counts_only_scanned_requests_as_awaiting_decision(): void
    {
        $dg = $this->makeUser('dg');
        $this->makeRequest('scannee');
        $this->makeRequest('soumise');
        $this->makeRequest('rejetee');

        $service = app(DgDashboardService::class);
        $filters = $service->normalizeFilters([]);
        $dashboard = $service->build($dg, $filters);

        $this->assertSame(1, $dashboard['kpis']['awaiting_dg']);
        $this->assertSame(2, $dashboard['kpis']['in_progress']);
        $this->assertCount(1, $dashboard['awaiting']);
    }

    public function test_service_returns_empty_states_when_no_data(): void
    {
        $dg = $this->makeUser('dg');

        $service = app(DgDashboardService::class);
        $dashboard = $service->build($dg, $service->normalizeFilters([]));

        $this->assertSame(0, $dashboard['kpis']['awaiting_dg']);
        $this->assertTrue($dashboard['awaiting']->isEmpty());
        $this->assertTrue($dashboard['urgent']->isEmpty());
        $this->assertSame(0, $dashboard['evolution']['total']);
    }

    public function test_notifications_reflect_real_pending_decisions(): void
    {
        $dg = $this->makeUser('dg');
        $this->makeRequest('scannee');

        $notifications = app(DgDashboardService::class)->notificationsFor($dg);

        $this->assertGreaterThanOrEqual(1, $notifications['total']);
        $this->assertStringContainsString('décision', $notifications['items'][0]['label']);
    }

    public function test_dg_can_view_deposited_document(): void
    {
        Storage::fake('public');
        $this->actingAsDg();
        $demande = $this->makeRequest('scannee', [
            'dg_signature_file' => 'demandes/signatures/signature-test.pdf',
        ]);
        Storage::disk('public')->put('demandes/signatures/signature-test.pdf', 'contenu-pdf');

        $this->get(route('dg.demandes.document', [$demande->id, 'signature']))
            ->assertOk();
    }

    public function test_document_route_rejects_unknown_type_and_missing_file(): void
    {
        $this->actingAsDg();
        $demande = $this->makeRequest('scannee');

        $this->get(route('dg.demandes.document', [$demande->id, 'autre']))->assertNotFound();
        $this->get(route('dg.demandes.document', [$demande->id, 'signature']))->assertNotFound();
    }

    public function test_document_route_is_denied_to_non_dg(): void
    {
        Storage::fake('public');
        $admin = $this->makeUser('admin');
        $this->actingAs($admin, 'dg');

        $demande = $this->makeRequest('scannee', [
            'dg_signature_file' => 'demandes/signatures/signature-test.pdf',
        ]);

        $this->get(route('dg.demandes.document', [$demande->id, 'signature']))
            ->assertRedirect();
    }

    public function test_detail_shows_workflow_history_and_document_links(): void
    {
        $this->actingAsDg();
        $demande = $this->makeRequest('scannee', [
            'dg_signature_file' => 'demandes/signatures/doc.pdf',
            'scan_file' => 'demandes/scans/doc.pdf',
            'workflow_history' => [
                ['action' => 'soumise → signee', 'comment' => 'Signature déposée', 'timestamp' => now()->subDay()->toIso8601String()],
                ['action' => 'signee → scannee', 'comment' => 'Scan déposé', 'timestamp' => now()->toIso8601String()],
            ],
        ]);

        $this->get(route('dg.demandes.show', $demande->id))
            ->assertOk()
            ->assertSee('Historique du workflow')
            ->assertSee('Signature déposée')
            ->assertSee('Scan déposé')
            ->assertSee(route('dg.demandes.document', [$demande->id, 'signature']))
            ->assertSee(route('dg.demandes.document', [$demande->id, 'scan']));
    }
}
