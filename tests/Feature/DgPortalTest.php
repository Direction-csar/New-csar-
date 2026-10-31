<?php

namespace Tests\Feature;

use App\Models\PublicRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Espace Directrice générale (guard « dg ») : liste, filtres, détail,
 * décisions et garde-fous du workflow.
 */
class DgPortalTest extends TestCase
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

    public function test_list_displays_real_subject_urgency_and_workflow_label(): void
    {
        $this->actingAsDg();
        $demande = $this->makeRequest('scannee');

        $this->get(route('dg.demandes.index'))
            ->assertOk()
            ->assertSee($demande->subject)
            ->assertSee($demande->tracking_code)
            ->assertSee('Scannée');
    }

    public function test_list_filters_by_workflow_status_on_server(): void
    {
        $this->actingAsDg();
        $scanned = $this->makeRequest('scannee', ['subject' => 'Dossier scanné unique']);
        $draft = $this->makeRequest('soumise', ['subject' => 'Dossier soumis unique']);

        $this->get(route('dg.demandes.index', ['workflow_status' => 'scannee']))
            ->assertOk()
            ->assertSee($scanned->tracking_code)
            ->assertDontSee($draft->tracking_code);
    }

    public function test_list_search_matches_tracking_code(): void
    {
        $this->actingAsDg();
        $target = $this->makeRequest('en_revue');
        $other = $this->makeRequest('en_revue');

        $this->get(route('dg.demandes.index', ['q' => $target->tracking_code]))
            ->assertOk()
            ->assertSee($target->tracking_code)
            ->assertDontSee($other->tracking_code);
    }

    public function test_detail_offers_decision_buttons_only_when_dossier_is_scanned(): void
    {
        $this->actingAsDg();
        $draft = $this->makeRequest('soumise');
        $scanned = $this->makeRequest('scannee');

        $this->get(route('dg.demandes.show', $draft->id))
            ->assertOk()
            ->assertDontSeeHtml('onclick="approveRequest(');

        $this->get(route('dg.demandes.show', $scanned->id))
            ->assertOk()
            ->assertSeeHtml('onclick="approveRequest(')
            ->assertSeeHtml('onclick="rejectRequest(');
    }

    private function makePersonnel(): \App\Models\Personnel
    {
        return \App\Models\Personnel::create([
            'prenoms_nom' => 'Agent Test', 'date_naissance' => '1990-01-01', 'lieu_naissance' => 'Dakar',
            'tranche_age' => '26-35', 'nationalite' => 'Senegalaise', 'numero_cni' => '1234567890123',
            'sexe' => 'Masculin', 'situation_matrimoniale' => 'Celibataire', 'contact_telephonique' => '770000002',
            'email' => 'agent@csar.test', 'groupe_sanguin' => 'O+', 'adresse_complete' => 'Dakar',
            'matricule' => 'MAT-TEST-1', 'date_recrutement_csar' => '2020-01-01', 'date_prise_service_csar' => '2020-02-01',
            'statut' => 'Fonctionnaire', 'poste_actuel' => 'Agent', 'direction_service' => 'DRH',
            'diplome_academique' => 'Licence', 'interet_nouvelles_responsabilites' => 'Oui', 'taille_vetements' => 'M',
            'contact_urgence_nom' => 'Urgence', 'contact_urgence_telephone' => '770000003',
            'contact_urgence_lien_parente' => 'Frere', 'statut_validation' => 'Valide',
        ]);
    }

    public function test_personnel_list_and_detail_load_with_real_data(): void
    {
        $this->actingAsDg();
        $employee = $this->makePersonnel();

        $this->get('/dg/personnel')->assertOk()->assertSee('Agent Test');
        $this->get('/dg/personnel/' . $employee->id)->assertOk()->assertSee('Agent Test');
    }

    public function test_map_page_and_data_load_with_real_requests(): void
    {
        $this->actingAsDg();
        $this->makeRequest('scannee');

        $this->get('/dg/map')->assertOk();
        $this->getJson('/dg/map/data')->assertOk()->assertJsonPath('success', true);
    }

    public function test_dg_can_approve_scanned_request_and_transmit_to_dtl(): void
    {
        $dg = $this->actingAsDg();
        $demande = $this->makeRequest('scannee', [
            'dg_signature_file' => 'demandes/signatures/doc.pdf',
            'scan_file' => 'demandes/scans/doc.pdf',
        ]);

        $this->putJson(route('dg.demandes.update', $demande->id), [
            'statut' => 'approuvee',
            'commentaire_admin' => 'Approuvé',
        ])->assertOk()->assertJson(['success' => true]);

        $demande->refresh();
        $this->assertSame('approuvee', $demande->workflow_status);
        $this->assertSame($dg->id, $demande->dg_approved_by);
        $this->assertNotNull($demande->dtl_transmitted_at);
    }

    public function test_dg_cannot_approve_unscanned_request(): void
    {
        $this->actingAsDg();
        $demande = $this->makeRequest('soumise');

        $this->putJson(route('dg.demandes.update', $demande->id), [
            'statut' => 'approuvee',
        ])->assertStatus(422)->assertJson(['success' => false]);

        $this->assertSame('soumise', $demande->fresh()->workflow_status);
    }

    public function test_dg_rejection_requires_a_reason(): void
    {
        $this->actingAsDg();
        $demande = $this->makeRequest('scannee');

        $this->putJson(route('dg.demandes.update', $demande->id), [
            'statut' => 'rejetee',
            'commentaire_admin' => '',
        ])->assertStatus(422)->assertJson(['success' => false]);

        $this->assertSame('scannee', $demande->fresh()->workflow_status);
    }

    public function test_dg_rejection_with_reason_is_recorded(): void
    {
        $this->actingAsDg();
        $demande = $this->makeRequest('scannee');

        $this->putJson(route('dg.demandes.update', $demande->id), [
            'statut' => 'rejetee',
            'public_comment' => 'Pièces insuffisantes',
        ])->assertOk()->assertJson(['success' => true]);

        $this->assertSame('rejetee', $demande->fresh()->workflow_status);
    }

    public function test_user_without_dg_role_cannot_access_dg_portal(): void
    {
        $admin = $this->makeUser('admin');
        $this->actingAs($admin, 'dg');

        $this->get(route('dg.demandes.index'))->assertRedirect();
    }

    public function test_dg_without_two_factor_is_sent_to_setup(): void
    {
        $dg = $this->makeUser('dg', false);
        $this->actingAs($dg, 'dg');

        $this->get(route('dg.demandes.index'))
            ->assertRedirect(route('dg.2fa.setup'));
    }

    public function test_admin_cannot_perform_dg_validation(): void
    {
        $admin = $this->makeUser('admin');
        $demande = $this->makeRequest('scannee');
        $this->actingAs($admin, 'admin');

        $this->post('/admin/demandes/' . $demande->id . '/dg-validate', [
            'dg_approved' => 1,
        ]);

        $fresh = $demande->fresh();
        $this->assertSame('scannee', $fresh->workflow_status);
        $this->assertNull($fresh->dg_approved_by);
    }
}
