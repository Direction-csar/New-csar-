<?php

namespace Tests\Feature;

use App\Models\PublicRequest;
use App\Models\RequestMission;
use App\Models\User;
use App\Services\RequestWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Correctifs issus de l'audit fonctionnel du workflow :
 * suppression restreinte et douce, pièces déposées uniquement si la
 * transition est possible, métadonnées DG toujours tracées, documents
 * servis par une route contrôlée, clôture possible par le DTL.
 */
class WorkflowHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Mail::fake();
        Queue::fake();
        Storage::fake('local');
        Storage::fake('public');
    }

    private function dossier(string $state = 'soumise'): PublicRequest
    {
        return PublicRequest::create([
            'tracking_code' => PublicRequest::generateTrackingCode(), 'type' => 'aide_alimentaire',
            'full_name' => 'Fictif Audit', 'email' => 'audit@example.test', 'phone' => '+221770001111',
            'address' => 'Adresse fictive', 'region' => 'Dakar', 'subject' => 'Objet fictif',
            'description' => 'Description fictive', 'request_date' => today(),
            'status' => RequestWorkflowService::LEGACY_MAP[$state], 'workflow_status' => $state,
        ]);
    }

    private function actor(string $role = 'admin'): User
    {
        $u = new User();
        $u->forceFill(['name' => 'Fictif '.$role, 'email' => uniqid().'@example.test', 'password' => bcrypt('fixture'), 'role' => $role, 'is_active' => true,
            'two_factor_enabled' => true, 'two_factor_secret' => 'JBSWY3DPEHPK3PXP']);
        $u->save();
        return $u;
    }

    private function switch(User $user, string $guard = 'admin'): void
    {
        \Illuminate\Support\Facades\Auth::forgetGuards();
        $this->actingAs($user, $guard);
    }

    /** C1 — un rôle non admin ne peut pas supprimer un dossier. */
    public function test_non_admin_roles_cannot_delete_a_request(): void
    {
        foreach (['scanneur', 'signataire', 'dg'] as $role) {
            $d = $this->dossier('en_revue');
            $this->switch($this->actor($role));
            $this->delete('/admin/demandes/'.$d->id);
            $this->assertNotNull($d->fresh(), "le rôle {$role} a pu supprimer le dossier");
            $this->assertNull($d->fresh()->deleted_at);
        }
    }

    /** C1 — un dossier décidé ou avec missions n'est pas supprimable, même par l'admin. */
    public function test_admin_cannot_delete_decided_or_missioned_requests(): void
    {
        $admin = $this->actor('admin');
        $this->switch($admin);

        foreach (['signee', 'scannee', 'validee_dg', 'approuvee', 'cloturee'] as $state) {
            $d = $this->dossier($state);
            $this->delete('/admin/demandes/'.$d->id);
            $this->assertNull($d->fresh()->deleted_at, "le dossier {$state} a été supprimé");
        }

        $d = $this->dossier('approuvee');
        RequestMission::create([
            'public_request_id' => $d->id, 'type' => 'inspection',
            'assigned_by' => $admin->id, 'assigned_to' => $admin->id,
            'instructions' => 'Fictif', 'status' => 'affectee',
        ]);
        $d->workflow_status = 'en_revue';
        $d->save();
        $this->delete('/admin/demandes/'.$d->id);
        $this->assertNull($d->fresh()->deleted_at, 'un dossier avec mission a été supprimé');
    }

    /** C1 — l'admin peut archiver un dossier en triage : suppression douce + trace. */
    public function test_admin_soft_deletes_a_triage_request_with_history(): void
    {
        $d = $this->dossier('en_revue');
        $this->switch($this->actor('admin'));
        $this->delete('/admin/demandes/'.$d->id)->assertRedirect();

        $this->assertNull(PublicRequest::find($d->id), 'le dossier archivé doit disparaître des requêtes');
        $trashed = PublicRequest::withTrashed()->find($d->id);
        $this->assertNotNull($trashed);
        $this->assertNotNull($trashed->deleted_at);
        $this->assertSame('en_revue', $trashed->workflow_status);
        $this->assertContains('Dossier supprimé', collect($trashed->workflow_history)->pluck('action')->all());
    }

    /** C1 — la suppression en masse ignore les dossiers protégés. */
    public function test_bulk_delete_skips_protected_requests(): void
    {
        $this->switch($this->actor('scanneur'));
        $d1 = $this->dossier('en_revue');
        $this->post('/admin/demandes/bulk-delete', ['demande_ids' => [$d1->id]]);
        $this->assertNull($d1->fresh()->deleted_at);

        $this->switch($this->actor('admin'));
        $ok = $this->dossier('en_revue');
        $protected = $this->dossier('approuvee');
        $this->post('/admin/demandes/bulk-delete', ['demande_ids' => [$ok->id, $protected->id]]);
        $this->assertNotNull($ok->fresh()->deleted_at);
        $this->assertNull($protected->fresh()->deleted_at);
    }

    /** C2 — le document signé ne peut pas être remplacé sur un dossier décidé. */
    public function test_signature_cannot_be_replaced_on_decided_requests(): void
    {
        $signataire = $this->actor('signataire');
        foreach (['approuvee', 'cloturee', 'rejetee', 'validee_dg'] as $state) {
            $d = $this->dossier($state);
            $old = 'demandes/signatures/original_'.$state.'.pdf';
            Storage::disk('local')->put($old, 'ORIGINAL');
            $d->update(['dg_signature_file' => $old]);

            $this->switch($signataire);
            $this->post('/admin/demandes/'.$d->id.'/signature', [
                'signature_file' => UploadedFile::fake()->create('nouveau.pdf', 20, 'application/pdf'),
            ]);

            $this->assertSame($old, $d->fresh()->dg_signature_file, "signature remplacée sur {$state}");
            $this->assertSame($state, $d->fresh()->workflow_status);
            Storage::disk('local')->assertExists($old);
        }
    }

    /** C5 — le scan ne peut pas être déposé sur un dossier non signé. */
    public function test_scan_cannot_be_uploaded_before_signature(): void
    {
        foreach (['admin', 'scanneur'] as $role) {
            $d = $this->dossier('en_revue');
            $this->switch($this->actor($role));
            $this->post('/admin/demandes/'.$d->id.'/scan', [
                'scan_file' => UploadedFile::fake()->create('scan.pdf', 20, 'application/pdf'),
            ]);
            $this->assertNull($d->fresh()->scan_file, "scan enregistré sans signature (rôle {$role})");
            $this->assertSame('en_revue', $d->fresh()->workflow_status);
            $this->assertCount(0, Storage::disk('local')->files('demandes/scans'));
        }
    }

    /** C2/C5 — dépôt nominal : la pièce est privée et le workflow avance. */
    public function test_happy_path_signature_and_scan_stay_on_private_disk(): void
    {
        $dg = $this->actor('dg');
        $this->switch($dg);

        $d = $this->dossier('en_revue');
        $this->post('/admin/demandes/'.$d->id.'/signature', [
            'signature_file' => UploadedFile::fake()->create('signature.pdf', 20, 'application/pdf'),
        ])->assertSessionHas('success');
        $d = $d->fresh();
        $this->assertSame('signee', $d->workflow_status);
        Storage::disk('local')->assertExists($d->dg_signature_file);
        Storage::disk('public')->assertMissing($d->dg_signature_file);

        $this->post('/admin/demandes/'.$d->id.'/scan', [
            'scan_file' => UploadedFile::fake()->create('scan.pdf', 20, 'application/pdf'),
        ])->assertSessionHas('success');
        $d = $d->fresh();
        $this->assertSame('scannee', $d->workflow_status);
        Storage::disk('local')->assertExists($d->scan_file);
    }

    /** C6 — la transition générique vers validee_dg trace toujours le décideur. */
    public function test_validee_dg_via_generic_endpoint_records_dg_metadata(): void
    {
        $dg = $this->actor('dg');
        $d = $this->dossier('scannee');
        $this->switch($dg);
        $this->postJson('/admin/demandes/'.$d->id.'/workflow', ['workflow_status' => 'validee_dg'])->assertOk();

        $d = $d->fresh();
        $this->assertSame('validee_dg', $d->workflow_status);
        $this->assertSame($dg->id, $d->dg_approved_by);
        $this->assertNotNull($d->dg_approved_at);
    }

    /** C6 — approve() refuse explicitement un acteur non DG. */
    public function test_admin_approve_route_is_rejected_for_non_dg(): void
    {
        $d = $this->dossier('scannee');
        $this->switch($this->actor('admin'));
        $this->post('/admin/demandes/'.$d->id.'/approve');
        $this->assertSame('scannee', $d->fresh()->workflow_status);
        $this->assertNull($d->fresh()->dg_approved_by);
    }

    /** C4 — le document est servi par la route contrôlée, disque privé et repli public. */
    public function test_document_route_serves_private_and_legacy_files(): void
    {
        $d = $this->dossier('signee');
        Storage::disk('local')->put('demandes/signatures/prive.pdf', 'PRIVE');
        $d->update(['dg_signature_file' => 'demandes/signatures/prive.pdf']);

        $this->switch($this->actor('admin'));
        $this->get('/admin/demandes/'.$d->id.'/document/signature')->assertOk();
        $this->get('/admin/demandes/'.$d->id.'/document/autre')->assertNotFound();

        // Fichier hérité de l'ancien stockage public : toujours servi.
        Storage::disk('public')->put('demandes/scans/ancien.pdf', 'ANCIEN');
        $d->update(['scan_file' => 'demandes/scans/ancien.pdf']);
        $this->get('/admin/demandes/'.$d->id.'/document/scan')->assertOk();

        \Illuminate\Support\Facades\Auth::forgetGuards();
        $this->get('/admin/demandes/'.$d->id.'/document/signature')->assertRedirect();
    }

    /** C7 — le DTL peut clôturer un dossier approuvé sans mission active. */
    public function test_dtl_can_close_an_approved_request_without_active_mission(): void
    {
        $dtl = $this->actor('dtl');
        $d = $this->dossier('approuvee');
        $d->update(['dtl_transmitted_at' => now()]);

        $this->switch($dtl, 'dtl');
        $this->post('/dtl/demandes/'.$d->id.'/cloture')->assertSessionHas('success');
        $this->assertSame('cloturee', $d->fresh()->workflow_status);

        // Avec une mission active, la clôture est refusée.
        $d2 = $this->dossier('approuvee');
        $d2->update(['dtl_transmitted_at' => now()]);
        RequestMission::create([
            'public_request_id' => $d2->id, 'type' => 'inspection',
            'assigned_by' => $dtl->id, 'assigned_to' => $dtl->id,
            'instructions' => 'Fictif', 'status' => 'affectee',
        ]);
        $this->post('/dtl/demandes/'.$d2->id.'/cloture');
        $this->assertSame('approuvee', $d2->fresh()->workflow_status);
    }
}
