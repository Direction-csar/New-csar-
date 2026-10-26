<?php

namespace Tests\Feature;

use App\Models\PublicRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Dépôt de signature/scan et rejet avec motif : autorisations par guard et par
 * rôle, transitions du workflow, pièces jointes et contournements.
 */
class DgDocumentsAndRejectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function makeUser(string $role): User
    {
        $user = User::create([
            'name' => 'Test ' . $role,
            'email' => $role . substr(md5(uniqid()), 0, 6) . '@csar.test',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $user->role = $role;
        $user->two_factor_enabled = true;
        $user->two_factor_secret = 'JBSWY3DPEHPK3PXP';
        $user->save();

        return $user->refresh();
    }

    private function makeRequest(string $workflowStatus, array $overrides = []): PublicRequest
    {
        return PublicRequest::create(array_merge([
            'tracking_code' => 'CSAR-DOC' . strtoupper(substr(md5(uniqid()), 0, 8)),
            'type' => 'aide_alimentaire',
            'status' => 'pending',
            'workflow_status' => $workflowStatus,
            'full_name' => 'Demandeur Test',
            'phone' => '770000000',
            'email' => 'demandeur@test.sn',
            'region' => 'Dakar',
            'address' => 'Rue de test, Dakar',
            'description' => 'Description de test.',
            'request_date' => now()->toDateString(),
            'subject' => 'Objet de test documents',
            'urgency' => 'haute',
        ], $overrides));
    }

    private function signatureFile(): UploadedFile
    {
        return UploadedFile::fake()->create('signature.pdf', 20, 'application/pdf');
    }

    private function scanFile(): UploadedFile
    {
        return UploadedFile::fake()->create('scan.pdf', 20, 'application/pdf');
    }

    public function test_dg_signature_upload_advances_request_to_signee(): void
    {
        $dg = $this->makeUser('dg');
        $demande = $this->makeRequest('en_revue');

        $this->actingAs($dg, 'dg')
            ->post(route('dg.demandes.signature', $demande->id), ['signature_file' => $this->signatureFile()])
            ->assertSessionHas('success');

        $demande->refresh();
        $this->assertSame('signee', $demande->workflow_status);
        $this->assertNotNull($demande->dg_signature_file);
        Storage::disk('public')->assertExists($demande->dg_signature_file);
    }

    public function test_dg_scan_upload_after_signature_advances_request_to_scannee(): void
    {
        $dg = $this->makeUser('dg');
        $demande = $this->makeRequest('signee', ['dg_signature_file' => 'demandes/signatures/x.pdf']);

        $this->actingAs($dg, 'dg')
            ->post(route('dg.demandes.scan', $demande->id), ['scan_file' => $this->scanFile()])
            ->assertSessionHas('success');

        $demande->refresh();
        $this->assertSame('scannee', $demande->workflow_status);
        Storage::disk('public')->assertExists($demande->scan_file);
    }

    public function test_scan_without_signature_is_refused_and_leaves_no_file(): void
    {
        $dg = $this->makeUser('dg');
        $demande = $this->makeRequest('en_revue');

        $this->actingAs($dg, 'dg')
            ->post(route('dg.demandes.scan', $demande->id), ['scan_file' => $this->scanFile()])
            ->assertSessionHas('error');

        $demande->refresh();
        $this->assertSame('en_revue', $demande->workflow_status);
        $this->assertNull($demande->scan_file);
        $this->assertSame([], Storage::disk('public')->allFiles('demandes/scans'));
    }

    public function test_document_upload_rejects_unsupported_file_type(): void
    {
        $dg = $this->makeUser('dg');
        $demande = $this->makeRequest('en_revue');

        $this->actingAs($dg, 'dg')
            ->post(route('dg.demandes.signature', $demande->id), [
                'signature_file' => UploadedFile::fake()->create('virus.exe', 10, 'application/x-msdownload'),
            ])
            ->assertSessionHasErrors('signature_file');

        $this->assertSame('en_revue', $demande->fresh()->workflow_status);
    }

    public function test_admin_guard_cannot_reach_dg_document_routes(): void
    {
        $admin = $this->makeUser('admin');
        $demande = $this->makeRequest('en_revue');

        $this->actingAs($admin, 'admin')
            ->post(route('dg.demandes.signature', $demande->id), ['signature_file' => $this->signatureFile()])
            ->assertRedirect(route('dg.login'));

        $this->assertSame('en_revue', $demande->fresh()->workflow_status);
        $this->assertNull($demande->fresh()->dg_signature_file);
    }

    public function test_non_dg_roles_on_dg_guard_are_refused_document_upload(): void
    {
        foreach (['admin', 'signataire', 'scanneur'] as $role) {
            $user = $this->makeUser($role);
            $demande = $this->makeRequest('en_revue');

            $this->actingAs($user, 'dg')
                ->post(route('dg.demandes.signature', $demande->id), ['signature_file' => $this->signatureFile()])
                ->assertRedirect('/');

            $this->assertSame('en_revue', $demande->fresh()->workflow_status, "rôle {$role}");
            $this->assertNull($demande->fresh()->dg_signature_file, "rôle {$role}");
        }
    }

    public function test_admin_role_cannot_sign_through_admin_route_and_no_file_is_stored(): void
    {
        $admin = $this->makeUser('admin');
        $demande = $this->makeRequest('en_revue');

        $this->actingAs($admin, 'admin')
            ->post(route('admin.demandes.signature', $demande->id), ['signature_file' => $this->signatureFile()])
            ->assertSessionHas('error');

        $demande->refresh();
        $this->assertSame('en_revue', $demande->workflow_status);
        $this->assertNull($demande->dg_signature_file);
        $this->assertSame([], Storage::disk('public')->allFiles('demandes/signatures'));
    }

    public function test_signataire_cannot_upload_scan_through_admin_route_and_no_file_is_stored(): void
    {
        $signataire = $this->makeUser('signataire');
        $demande = $this->makeRequest('signee', ['dg_signature_file' => 'demandes/signatures/a.pdf']);
        $scan = \Illuminate\Http\UploadedFile::fake()->create('scan.pdf', 10, 'application/pdf');

        $this->actingAs($signataire, 'admin')
            ->post(route('admin.demandes.scan', $demande->id), ['scan_file' => $scan])
            ->assertForbidden();

        $demande->refresh();
        $this->assertSame('signee', $demande->workflow_status);
        $this->assertNull($demande->scan_file);
        $this->assertSame([], Storage::disk('public')->allFiles('demandes/scans'));
    }

    public function test_signataire_role_can_still_sign_through_admin_route(): void
    {
        $signataire = $this->makeUser('signataire');
        $demande = $this->makeRequest('en_revue');

        $this->actingAs($signataire, 'admin')
            ->post(route('admin.demandes.signature', $demande->id), ['signature_file' => $this->signatureFile()]);

        $this->assertSame('signee', $demande->fresh()->workflow_status);
    }

    public function test_reject_requires_a_reason_on_server(): void
    {
        $dg = $this->makeUser('dg');
        $demande = $this->makeRequest('scannee', ['dg_signature_file' => 'a.pdf', 'scan_file' => 'b.pdf']);

        $this->actingAs($dg, 'dg')
            ->putJson(route('dg.demandes.update', $demande->id), ['statut' => 'rejetee'])
            ->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->actingAs($dg, 'dg')
            ->putJson(route('dg.demandes.update', $demande->id), ['statut' => 'rejetee', 'commentaire_admin' => 'court'])
            ->assertStatus(422);

        $this->assertSame('scannee', $demande->fresh()->workflow_status);
    }

    public function test_reject_with_reason_is_recorded_in_workflow_history(): void
    {
        $dg = $this->makeUser('dg');
        $demande = $this->makeRequest('scannee', ['dg_signature_file' => 'a.pdf', 'scan_file' => 'b.pdf']);
        $reason = 'Pièces justificatives insuffisantes pour ce dossier';

        $this->actingAs($dg, 'dg')
            ->putJson(route('dg.demandes.update', $demande->id), ['statut' => 'rejetee', 'commentaire_admin' => $reason])
            ->assertOk()
            ->assertJsonPath('success', true);

        $demande->refresh();
        $this->assertSame('rejetee', $demande->workflow_status);
        $this->assertStringContainsString($reason, json_encode($demande->workflow_history, JSON_UNESCAPED_UNICODE));
    }

    public function test_admin_cannot_reject_scanned_request_through_dg_route(): void
    {
        $admin = $this->makeUser('admin');
        $demande = $this->makeRequest('scannee', ['dg_signature_file' => 'a.pdf', 'scan_file' => 'b.pdf']);

        $this->actingAs($admin, 'admin')
            ->putJson(route('dg.demandes.update', $demande->id), ['statut' => 'rejetee', 'commentaire_admin' => 'Motif suffisamment long'])
            ->assertRedirect(route('dg.login'));

        $this->assertSame('scannee', $demande->fresh()->workflow_status);
    }

    public function test_dg_detail_page_uses_bootstrap_modal_instead_of_prompt(): void
    {
        $dg = $this->makeUser('dg');
        $demande = $this->makeRequest('scannee', ['dg_signature_file' => 'a.pdf', 'scan_file' => 'b.pdf']);

        $this->actingAs($dg, 'dg')
            ->get(route('dg.demandes.show', $demande->id))
            ->assertOk()
            ->assertSee('id="rejectModal"', false)
            ->assertSee('id="rejectForm"', false)
            ->assertSee('id="approveModal"', false)
            ->assertSee('id="approveForm"', false)
            ->assertDontSee("prompt('Motif", false)
            ->assertDontSee("prompt('Commentaire", false);
    }
}
