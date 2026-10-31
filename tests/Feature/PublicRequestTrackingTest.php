<?php

namespace Tests\Feature;

use App\Jobs\SendRequestTrackingCode;
use App\Jobs\SendRequestWorkflowNotification;
use App\Mail\RequestTrackingCode;
use App\Mail\RequestWorkflowUpdated;
use App\Models\PublicRequest;
use App\Models\User;
use App\Services\RequestTrackingService;
use App\Services\RequestWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PublicRequestTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Mail::fake();
        Queue::fake();
    }

    private function dossier(string $state = 'soumise'): PublicRequest
    {
        return PublicRequest::create([
            'tracking_code' => PublicRequest::generateTrackingCode(), 'type' => 'aide_alimentaire',
            'full_name' => 'Fictif Audit', 'email' => 'audit@example.test', 'phone' => '+221770001111',
            'address' => 'Adresse fictive', 'region' => 'Dakar', 'subject' => 'Objet fictif',
            'description' => 'Description fictive', 'request_date' => today(),
            'status' => RequestWorkflowService::LEGACY_MAP[$state], 'workflow_status' => $state,
            'admin_comment' => 'SECRET_INTERNE', 'document_notes' => 'NOTE_SIGNATURE_INTERNE',
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

    private function challenge(PublicRequest $dossier, string $email = 'audit@example.test'): SendRequestTrackingCode
    {
        $this->from('/suivi')->post('/suivi', ['tracking_code' => $dossier->tracking_code, 'email' => $email])
            ->assertRedirect('/suivi')->assertSessionHas('tracking_message', RequestTrackingService::MESSAGE);
        return Queue::pushed(SendRequestTrackingCode::class)->last();
    }

    private function unlock(PublicRequest $dossier): void
    {
        $job = $this->challenge($dossier);
        $job->handle();
        Mail::assertSent(RequestTrackingCode::class, fn ($mail) => $mail->code === $job->code && $mail->hasTo($dossier->email));
        $this->post('/suivi', ['action' => 'confirm', 'verification_code' => $job->code])
            ->assertRedirect(route('suivi.direct', ['code' => $dossier->tracking_code]));
    }

    public function test_reference_and_phone_are_not_a_proof_and_pdf_is_protected(): void
    {
        $d = $this->dossier();
        foreach (['/suivi', '/fr/suivre-ma-demande'] as $path) {
            $this->postJson($path, ['tracking_code' => $d->tracking_code, 'phone' => $d->phone])->assertUnprocessable();
            $this->get($path.'?code='.$d->tracking_code)->assertOk()->assertDontSee($d->full_name);
        }
        foreach (['/telecharger-pdf/'.$d->tracking_code, '/fr/suivre-ma-demande/'.$d->tracking_code.'/pdf'] as $path) {
            $this->get($path.'?phone='.$d->phone)->assertRedirect();
        }
    }

    public function test_wrong_email_and_unknown_reference_get_the_same_response_and_no_mail(): void
    {
        $d = $this->dossier();
        $wrong = $this->challenge($d, 'other@example.test');
        $wrong->handle();
        $d->tracking_code = 'CSAR-UNKNOWN';
        $unknown = $this->challenge($d);
        $unknown->handle();
        Mail::assertNothingSent();
        $this->post('/suivi', ['action' => 'confirm', 'verification_code' => $wrong->code])->assertSessionHas('tracking_error');
    }

    public function test_valid_code_grants_only_its_dossier_and_is_consumed(): void
    {
        $d = $this->dossier();
        $other = $this->dossier();
        $job = $this->challenge($d);
        $this->post('/suivi', ['action' => 'confirm', 'verification_code' => $job->code])->assertRedirect();
        foreach (['/suivi', '/fr/suivre-ma-demande'] as $url) {
            $this->get($url.'?code='.$d->tracking_code)->assertOk()->assertSee($d->full_name)->assertDontSee('SECRET_INTERNE')
                ->assertHeader('Referrer-Policy', 'no-referrer');
            $this->get($url.'?code='.$other->tracking_code)->assertDontSee($other->description);
        }
        $this->get('/telecharger-pdf/'.$other->tracking_code)->assertRedirect();
        $this->post('/suivi', ['action' => 'confirm', 'verification_code' => $job->code])->assertSessionHas('tracking_error');
        $this->travel(21)->minutes();
        $this->get('/suivi?code='.$d->tracking_code)->assertDontSee($d->description);
    }

    public function test_otp_expires_and_is_bound_to_the_issuing_session(): void
    {
        $d = $this->dossier();
        $job = $this->challenge($d);
        DB::table('request_tracking_challenges')->where('id', $job->challengeId)->update(['session_hash' => 'other-session']);
        $this->post('/suivi', ['action' => 'confirm', 'verification_code' => $job->code])->assertSessionHas('tracking_error');
        $job = $this->challenge($d);
        $this->travel(11)->minutes();
        $this->post('/suivi', ['action' => 'confirm', 'verification_code' => $job->code])->assertSessionHas('tracking_error');
        $this->get('/suivi?code='.$d->tracking_code)->assertDontSee($d->description);
    }

    public function test_five_wrong_attempts_block_even_the_correct_code(): void
    {
        $d = $this->dossier();
        $job = $this->challenge($d);
        for ($i = 0; $i < 5; $i++) {
            $this->post('/suivi', ['action' => 'confirm', 'verification_code' => '000000'])->assertSessionHas('tracking_error');
        }
        $this->post('/suivi', ['action' => 'confirm', 'verification_code' => $job->code])->assertSessionHas('tracking_error');
        $this->assertDatabaseHas('request_tracking_challenges', ['id' => $job->challengeId, 'attempts' => 5, 'consumed_at' => null]);
    }

    public function test_rate_limit_is_shared_across_aliases_and_issue_quota_is_bounded(): void
    {
        $d = $this->dossier();
        for ($i = 0; $i < 10; $i++) {
            $url = $i % 2 ? '/suivi' : '/fr/suivre-ma-demande';
            $this->post($url, ['tracking_code' => $d->tracking_code, 'email' => $d->email])->assertRedirect();
        }
        Queue::assertPushed(SendRequestTrackingCode::class, 3);
        $this->post('/suivi', ['tracking_code' => $d->tracking_code, 'email' => $d->email])->assertStatus(429);
    }

    public function test_public_reason_timeline_pdf_and_qr_never_expose_internal_notes(): void
    {
        $d = $this->dossier();
        $d = app(RequestWorkflowService::class)->advance($d, 'rejetee', 'SECRET_TRANSITION', $this->actor(), 'Motif public fictif');
        $this->unlock($d);
        foreach (['/suivi', '/fr/suivre-ma-demande'] as $url) {
            $this->get($url.'?code='.$d->tracking_code)->assertOk()->assertSee('Rejetée')->assertSee('Motif public fictif')
                ->assertDontSee('SECRET_INTERNE')->assertDontSee('SECRET_TRANSITION')->assertDontSee('NOTE_SIGNATURE_INTERNE');
        }
        $projection = $d->publicSummary();
        $this->assertFalse(property_exists($projection, 'admin_comment'));
        $html = view('public.pdf.request', ['request' => $projection])->render();
        $this->assertStringContainsString('Motif public fictif', $html);
        $this->assertStringNotContainsString('SECRET_', $html);
        $this->get('/fr/verifier/'.$d->tracking_code)->assertRedirect(route('suivi.direct', ['code' => $d->tracking_code]));
        $pdf = $this->get('/telecharger-pdf/'.$d->tracking_code)->assertOk();
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
    }

    public function test_all_public_states_use_the_same_label_and_legacy_comments_stay_private(): void
    {
        $d = $this->dossier();
        $this->unlock($d);
        foreach (array_keys(RequestWorkflowService::GRAPH) as $state) {
            // Fixture setup deliberately creates contradictory legacy status.
            $d->update(['workflow_status' => $state, 'status' => 'pending', 'workflow_history' => [
                ['action' => 'Transition: soumise → '.$state, 'comment' => 'SECRET_LEGACY', 'timestamp' => now()->toIso8601String()],
            ]]);
            foreach (['/suivi', '/fr/suivre-ma-demande'] as $url) {
                $this->get($url.'?code='.$d->tracking_code)->assertSee(PublicRequest::publicStatusLabel($state))->assertDontSee('SECRET_LEGACY');
            }
        }
    }

    public function test_stale_decision_cannot_overwrite_rejection_or_history(): void
    {
        $d = $this->dossier('en_revue');
        $stale = $d->fresh();
        $svc = app(RequestWorkflowService::class);
        $actor = $this->actor();
        $svc->advance($d, 'rejetee', 'Note interne', $actor, 'Motif de rejet public');
        $history = $d->fresh()->workflow_history;
        try {
            $svc->advance($stale, 'document_attente', null, $actor, 'Pièce attendue');
            $this->fail('Stale decision accepted');
        } catch (ValidationException) {
            $this->assertSame('rejetee', $d->fresh()->workflow_status);
            $this->assertSame($history, $d->fresh()->workflow_history);
        }
        $entry = $history[0];
        $this->assertSame('en_revue', $entry['from_status']);
        $this->assertSame('rejetee', $entry['to_status']);
        $this->assertSame($actor->id, $entry['user_id']);
        $this->assertNotEmpty($entry['timestamp']);
    }

    public function test_old_instances_append_history_without_losing_other_actions(): void
    {
        $d = $this->dossier();
        $other = $d->fresh();
        $d->logWorkflowAction('Instruction DTL', 'Note A');
        $other->logWorkflowAction('Rapport validé par le DTL', 'Note B');
        $this->assertCount(2, $d->fresh()->workflow_history);
    }

    public function test_duplicate_action_cannot_reject_closed_or_signed_requests(): void
    {
        $original = $this->dossier();
        $this->actingAs($this->actor(), 'admin');
        foreach (['cloturee', 'approuvee', 'signee', 'rejetee'] as $state) {
            $d = $this->dossier($state);
            $this->post('/admin/demandes/'.$d->id.'/duplicate', ['original_id' => $original->id])->assertRedirect();
            $this->assertSame($state, $d->fresh()->workflow_status);
            $this->assertFalse((bool) $d->fresh()->is_duplicate);
        }
        $d = $this->dossier();
        $this->post('/admin/demandes/'.$d->id.'/duplicate', ['original_id' => $original->id])->assertRedirect();
        $this->assertSame('rejetee', $d->fresh()->workflow_status);
        $this->assertTrue($d->fresh()->is_duplicate);
        $this->assertStringNotContainsString($original->tracking_code, json_encode($d->fresh()->publicSummary()));
    }

    public function test_notification_is_committed_once_and_never_sent_for_rollback(): void
    {
        $d = $this->dossier();
        $actor = $this->actor();
        DB::beginTransaction();
        $next = app(RequestWorkflowService::class)->advance($d, 'en_revue', null, $actor);
        Queue::assertNotPushed(SendRequestWorkflowNotification::class);
        DB::rollBack();
        $this->assertDatabaseCount('request_notification_deliveries', 0);
        $d = $d->fresh();
        app(RequestWorkflowService::class)->advance($d, 'en_revue', null, $actor);
        app(RequestWorkflowService::class)->advance($d, 'en_revue', null, $actor);
        Queue::assertPushed(SendRequestWorkflowNotification::class, 1);
        $job = Queue::pushed(SendRequestWorkflowNotification::class)->first();
        $job->handle();
        $job->handle();
        Mail::assertSent(RequestWorkflowUpdated::class, 1);
        $this->assertDatabaseHas('request_notification_deliveries', ['id' => $job->deliveryId, 'state' => 'accepted']);
    }

    public function test_notification_failure_is_ambiguous_not_delivered_and_not_retried(): void
    {
        $d = $this->dossier();
        app(RequestWorkflowService::class)->advance($d, 'en_revue', null, $this->actor());
        $job = Queue::pushed(SendRequestWorkflowNotification::class)->first();
        Mail::shouldReceive('to')->once()->andReturnSelf();
        Mail::shouldReceive('send')->once()->andThrow(new \RuntimeException('SECRET_PROVIDER_PAYLOAD'));
        \Illuminate\Support\Facades\Log::spy();
        $job->handle();
        $job->handle();
        $this->assertDatabaseHas('request_notification_deliveries', [
            'id' => $job->deliveryId, 'state' => 'failed_or_unknown', 'accepted_at' => null,
        ]);
        \Illuminate\Support\Facades\Log::shouldHaveReceived('warning')
            ->with('request_notification_failed', ['delivery_id' => $job->deliveryId])->once();
    }

    public function test_http_circuit_dg_dtl_inspector_and_closure_keeps_public_history_and_notifications(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $d = $this->dossier();
        $actors = [];
        foreach (['admin', 'dg', 'dtl', 'inspecteur', 'drh'] as $role) {
            $actors[$role] = $this->actor($role);
        }
        $switch = function (string $role) use ($actors) {
            \Illuminate\Support\Facades\Auth::forgetGuards();
            $this->actingAs($actors[$role], $role);
        };
        $switch('admin');
        foreach (['en_revue', 'document_attente'] as $state) {
            $this->postJson('/admin/demandes/'.$d->id.'/workflow', [
                'workflow_status' => $state, 'comment' => 'SECRET_ETUDE', 'public_comment' => 'Complément fictif requis',
            ])->assertOk();
            $this->assertSame($state, $d->fresh()->workflow_status);
        }
        $switch('dg');
        $this->post('/dg/demandes/'.$d->id.'/signature', ['signature_file' => \Illuminate\Http\UploadedFile::fake()->create('signature.pdf', 20, 'application/pdf')])->assertSessionHas('success');
        $this->assertSame('signee', $d->fresh()->workflow_status);
        $this->post('/dg/demandes/'.$d->id.'/scan', ['scan_file' => \Illuminate\Http\UploadedFile::fake()->create('scan.pdf', 20, 'application/pdf')])->assertSessionHas('success');
        $this->putJson('/dg/demandes/'.$d->id, ['statut' => 'approuvee', 'commentaire_admin' => 'SECRET_DG'])->assertOk();
        $this->assertSame('approuvee', $d->fresh()->workflow_status);
        $this->assertNotNull($d->fresh()->dtl_transmitted_at);
        $switch('dtl');
        $this->get('/dtl/demandes/'.$d->id)->assertOk();
        $this->post('/dtl/demandes/'.$d->id.'/missions', [
            'type' => 'inspection', 'assigned_to' => $actors['inspecteur']->id, 'instructions' => 'SECRET_MISSION',
        ])->assertSessionHas('success');
        $mission = \App\Models\RequestMission::sole();
        $switch('admin');
        $this->postJson('/admin/demandes/'.$d->id.'/workflow', ['workflow_status' => 'cloturee'])->assertUnprocessable();
        $switch('inspecteur');
        $this->post('/inspecteur/missions/'.$mission->id.'/start')->assertSessionHas('success');
        $this->post('/inspecteur/missions/'.$mission->id.'/report', ['report' => 'Rapport fictif complet de visite du demandeur.'])->assertRedirect();
        $switch('dtl');
        $this->post('/dtl/missions/'.$mission->id.'/validate')->assertSessionHas('success');
        $this->assertSame('terminee', $mission->fresh()->status);
        $this->assertSame('approuvee', $d->fresh()->workflow_status);
        $switch('admin');
        $this->postJson('/admin/demandes/'.$d->id.'/workflow', ['workflow_status' => 'cloturee'])->assertOk();
        $this->assertSame('completed', $d->fresh()->status);
        Queue::assertPushed(SendRequestWorkflowNotification::class, 7);
        $this->assertDatabaseCount('request_notification_deliveries', 7);
        foreach (['admin', 'dg', 'dtl'] as $role) {
            $switch($role);
            $this->get('/'.$role.'/demandes/'.$d->id)->assertOk();
        }
        $switch('drh');
        $this->get('/dtl/demandes/'.$d->id)->assertRedirect(route('dtl.login'));
        \Illuminate\Support\Facades\Auth::forgetGuards();
        config(['auth.defaults.guard' => 'web']);
        $this->unlock($d);
        $this->get('/suivi?code='.$d->tracking_code)->assertSee('Clôturée')
            ->assertSee('Mission en cours')->assertSee('Rapport de mission validé')
            ->assertDontSee('SECRET_ETUDE')->assertDontSee('SECRET_DG')->assertDontSee('SECRET_MISSION');
    }
}
