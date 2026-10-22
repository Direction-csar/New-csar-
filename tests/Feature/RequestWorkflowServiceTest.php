<?php

namespace Tests\Feature;

use App\Models\PublicRequest;
use App\Models\RequestMission;
use App\Models\User;
use App\Services\RequestWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Tests de la machine à états du workflow des demandes.
 * Couvre : transitions illégales, rôles, motifs obligatoires,
 * délégation auditée, transmission DTL, clôture avec missions actives.
 */
class RequestWorkflowServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): RequestWorkflowService
    {
        return app(RequestWorkflowService::class);
    }

    private function makeUser(string $role): User
    {
        $user = User::create([
            'name' => 'Test ' . $role . ' ' . substr(md5(uniqid()), 0, 4),
            'email' => $role . substr(md5(uniqid()), 0, 6) . '@csar.test',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        // Assignation directe : 'role' n'est pas toujours mass-assignable
        $user->role = $role;
        $user->save();
        return $user->refresh();
    }

    private function makeRequest(string $workflowStatus = 'soumise'): PublicRequest
    {
        return PublicRequest::create([
            'tracking_code' => 'CSAR-TEST' . strtoupper(substr(md5(uniqid()), 0, 8)),
            'type' => 'aide_alimentaire',
            'status' => 'pending',
            'workflow_status' => $workflowStatus,
            'full_name' => 'Demandeur Test',
            'phone' => '770000000',
            'email' => 'demandeur@test.sn',
            'region' => 'Dakar',
            'address' => 'Rue de test, Dakar',
            'description' => 'Description de test pour le scénario workflow.',
            'request_date' => now()->toDateString(),
            'subject' => 'Test',
        ]);
    }

    /** @test Transition illégale directe soumise → approuvee refusée */
    public function test_illegal_transition_is_rejected(): void
    {
        $demande = $this->makeRequest('soumise');
        $admin = $this->makeUser('admin');

        $this->expectException(ValidationException::class);
        $this->service()->advance($demande, 'approuvee', null, $admin);
    }

    /** @test Chaîne légale complète : soumise → en_revue → document_attente → signee → scannee → validee_dg → approuvee */
    public function test_full_legal_chain_reaches_approuvee_and_transmits_to_dtl(): void
    {
        $demande = $this->makeRequest('soumise');
        $admin = $this->makeUser('admin');
        $signataire = $this->makeUser('signataire');
        $scanneur = $this->makeUser('scanneur');
        $dg = $this->makeUser('dg');
        $this->makeUser('dtl');
        $svc = $this->service();

        $demande = $svc->advance($demande, 'en_revue', null, $admin);
        $this->assertSame('en_revue', $demande->workflow_status);
        $this->assertSame('pending', $demande->status);

        $demande = $svc->advance($demande, 'document_attente', 'Dossier prêt pour signature', $admin);

        // signee exige le fichier de signature, posé par le signataire/DG
        $demande->update(['dg_signature_file' => 'demandes/signatures/doc.pdf']);
        $demande = $svc->advance($demande, 'signee', 'Signée par la Directrice', $signataire);

        // scannee exige le scan (opération technique : admin/scanneur)
        $demande->update(['scan_file' => 'demandes/scans/scan.pdf']);
        $demande = $svc->advance($demande, 'scannee', 'Scan enregistré', $scanneur);

        $demande = $svc->dgValidate($demande, true, 'Validation DG', $dg);
        // La validation DG enchaîne automatiquement jusqu'à « approuvee »
        // (décision officielle) afin de déclencher la transmission au DTL.
        $this->assertSame('approuvee', $demande->workflow_status);
        $this->assertSame('approved', $demande->status);
        $this->assertNotNull($demande->dg_approved_at);
        $this->assertSame($dg->id, $demande->dg_approved_by);

        // L'historique conserve néanmoins le passage par validee_dg
        $actions = collect($demande->workflow_history)->pluck('action')->implode('|');
        $this->assertStringContainsString('validee_dg', $actions);

        // Transmission automatique au DTL, signée par la DG
        $this->assertNotNull($demande->dtl_transmitted_at);
        $this->assertSame($dg->id, $demande->dtl_transmitted_by);

        // Historique trace le tout
        $actions = collect($demande->workflow_history)->pluck('action')->implode('|');
        $this->assertStringContainsString('Transmise au DTL', $actions);
    }

    /** @test signee sans fichier de signature est refusé */
    public function test_signee_requires_signature_file(): void
    {
        $demande = $this->makeRequest('document_attente');
        $dg = $this->makeUser('dg');

        $this->expectException(ValidationException::class);
        $this->service()->advance($demande, 'signee', 'Signature', $dg);
    }

    /** @test scannee sans fichier de scan est refusé */
    public function test_scannee_requires_scan_file(): void
    {
        $demande = $this->makeRequest('signee');
        $dg = $this->makeUser('dg');

        $this->expectException(ValidationException::class);
        $this->service()->advance($demande, 'scannee', 'Scan', $dg);
    }

    /** @test Le rejet exige un motif */
    public function test_rejection_requires_comment(): void
    {
        $demande = $this->makeRequest('soumise');
        $dg = $this->makeUser('dg');

        $this->expectException(ValidationException::class);
        $this->service()->dgValidate($demande, false, '', $dg);
    }

    /** @test Un rôle non autorisé (ex: inspecteur) ne peut pas avancer le workflow */
    public function test_unauthorized_role_cannot_advance(): void
    {
        $demande = $this->makeRequest('soumise');
        $inspecteur = $this->makeUser('inspecteur');

        $this->expectException(ValidationException::class);
        $this->service()->advance($demande, 'en_revue', null, $inspecteur);
    }

    /** @test L'admin ne peut pas valider/approuver à la place de la DG */
    public function test_admin_cannot_validate_on_behalf_of_dg(): void
    {
        $demande = $this->makeRequest('scannee');
        $admin = $this->makeUser('admin');

        $this->expectException(ValidationException::class);
        $this->service()->dgValidate($demande, true, 'Tentative admin', $admin);
    }

    /** @test L'admin ne peut pas marquer la demande comme signée */
    public function test_admin_cannot_sign(): void
    {
        $demande = $this->makeRequest('document_attente');
        $demande->update(['dg_signature_file' => 'demandes/signatures/doc.pdf']);
        $admin = $this->makeUser('admin');

        $this->expectException(ValidationException::class);
        $this->service()->advance($demande, 'signee', 'Tentative admin', $admin);
    }

    /** @test L'admin peut rejeter en triage (avant signature) avec motif */
    public function test_admin_can_reject_in_triage(): void
    {
        $demande = $this->makeRequest('en_revue');
        $admin = $this->makeUser('admin');

        $demande = $this->service()->advance($demande, 'rejetee', 'Dossier manifestement irrecevable', $admin);

        $this->assertSame('rejetee', $demande->workflow_status);
        $this->assertSame('rejected', $demande->status);
    }

    /** @test L'admin ne peut pas rejeter une demande déjà signée/scannée */
    public function test_admin_cannot_reject_after_signature(): void
    {
        $demande = $this->makeRequest('scannee');
        $admin = $this->makeUser('admin');

        $this->expectException(ValidationException::class);
        $this->service()->advance($demande, 'rejetee', 'Tentative admin', $admin);
    }

    /** @test La DG peut rejeter une demande scannée avec motif */
    public function test_dg_can_reject_scanned_request(): void
    {
        $demande = $this->makeRequest('scannee');
        $dg = $this->makeUser('dg');

        $demande = $this->service()->advance($demande, 'rejetee', 'Rejet décidé par la Direction', $dg);

        $this->assertSame('rejetee', $demande->workflow_status);
    }

    /** @test La clôture est impossible avec des missions actives */
    public function test_cloture_blocked_with_active_missions(): void
    {
        $demande = $this->makeRequest('approuvee');
        $admin = $this->makeUser('admin');
        $inspecteur = $this->makeUser('inspecteur');

        RequestMission::create([
            'public_request_id' => $demande->id,
            'type' => RequestMission::TYPE_INSPECTION,
            'assigned_to' => $inspecteur->id,
            'assigned_by' => $admin->id,
            'instructions' => 'Inspecter le site',
            'status' => 'en_cours',
        ]);

        $this->expectException(ValidationException::class);
        $this->service()->advance($demande, 'cloturee', 'Clôture', $admin);
    }

    /** @test La clôture passe quand toutes les missions sont terminées */
    public function test_cloture_allowed_when_missions_done(): void
    {
        $demande = $this->makeRequest('approuvee');
        $admin = $this->makeUser('admin');
        $inspecteur = $this->makeUser('inspecteur');

        RequestMission::create([
            'public_request_id' => $demande->id,
            'type' => RequestMission::TYPE_INSPECTION,
            'assigned_to' => $inspecteur->id,
            'assigned_by' => $admin->id,
            'instructions' => 'Inspecter le site',
            'status' => 'terminee',
            'completed_at' => now(),
        ]);

        $demande = $this->service()->advance($demande, 'cloturee', 'Dossier complet', $admin);
        $this->assertSame('cloturee', $demande->workflow_status);
        $this->assertSame('completed', $demande->status);
    }

    /** @test Les transitions permises depuis scannee sont validee_dg/rejetee uniquement */
    public function test_transitions_from_scannee(): void
    {
        $allowed = $this->service()->transitionsFrom('scannee');
        $this->assertSame(['validee_dg', 'rejetee'], $allowed);
    }
}
