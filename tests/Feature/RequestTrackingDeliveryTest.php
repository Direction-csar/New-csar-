<?php

namespace Tests\Feature;

use App\Jobs\SendRequestTrackingCode;
use App\Mail\RequestTrackingCode;
use App\Models\PublicRequest;
use App\Services\RequestTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Circuit d'envoi du code OTP : disponibilité du canal, remise au fournisseur,
 * erreurs de transport, état présenté au visiteur et absence de fuite.
 */
class RequestTrackingDeliveryTest extends TestCase
{
    use RefreshDatabase;

    private const EMAIL = 'test-autorise@example.test';

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    private function dossier(): PublicRequest
    {
        return PublicRequest::create([
            'name' => 'Fictif', 'full_name' => 'Fictif Test', 'email' => self::EMAIL, 'phone' => '+221771234567',
            'subject' => 'Objet fictif OTP', 'type' => 'aide_alimentaire', 'address' => '', 'description' => 'Fictif',
            'tracking_code' => 'CSAR-OTPTEST00001', 'status' => 'pending', 'workflow_status' => 'soumise',
            'request_date' => now()->toDateString(), 'region' => 'Dakar',
        ]);
    }

    private function requestCode(string $email = self::EMAIL): ?SendRequestTrackingCode
    {
        $this->from('/suivi')->post('/suivi', ['tracking_code' => 'CSAR-OTPTEST00001', 'email' => $email])->assertRedirect('/suivi');
        return Queue::pushed(SendRequestTrackingCode::class)->last();
    }

    private function useBrevo(): void
    {
        config([
            'mail.default' => 'brevo-api',
            'mail.mailers.brevo-api' => ['transport' => 'brevo-api'],
            'services.brevo.api_key' => 'cle-de-test-fictive',
            'services.brevo.sender_email' => 'noreply@csar.sn',
        ]);
        app('mail.manager')->forgetMailers();
    }

    private function challengeRow(): object
    {
        return DB::table('request_tracking_challenges')->find(Queue::pushed(SendRequestTrackingCode::class)->last()->challengeId);
    }

    public function test_inactive_mail_transport_shows_unavailable_and_issues_nothing(): void
    {
        $this->dossier();
        config(['mail.default' => 'log']);

        $this->get('/suivi')->assertSee('temporairement indisponible');
        $this->from('/suivi')->post('/suivi', ['action' => 'lookup', 'tracking_code' => 'CSAR-OTPTEST00001'])
            ->assertSessionHas('tracking_error', RequestTrackingService::UNAVAILABLE)
            ->assertSessionMissing('tracking_verify');
        $this->from('/suivi')->post('/suivi', ['tracking_code' => 'CSAR-OTPTEST00001', 'email' => self::EMAIL])
            ->assertSessionHas('tracking_error', RequestTrackingService::UNAVAILABLE)
            ->assertSessionMissing('tracking_prompt');
        $this->assertDatabaseCount('request_tracking_challenges', 0);
        Queue::assertNothingPushed();
    }

    public function test_mail_contains_html_and_text_with_code_and_official_logo(): void
    {
        $html = (new RequestTrackingCode('482913'))->render();
        $this->assertStringContainsString('482913', $html);
        $this->assertStringContainsString('images/csar-logo.png', $html);
        $this->assertStringContainsString('10 minutes', $html);

        Mail::fake();
        $this->dossier();
        $this->requestCode()->handle();
        Mail::assertSent(RequestTrackingCode::class, function (RequestTrackingCode $m) {
            $m->build();
            return $m->hasTo(self::EMAIL) && $m->textView === 'emails.request-tracking-code'
                && $m->view === 'emails.request-tracking-code-html';
        });
        $this->assertSame('accepted', $this->challengeRow()->delivery_status);
    }

    public function test_brevo_acceptance_is_recorded_with_provider_message_id(): void
    {
        $this->useBrevo();
        Http::fake(['api.brevo.com/*' => Http::response(['messageId' => '<202611021200.123@smtp-relay.mailin.fr>'], 201)]);
        $this->dossier();
        $this->requestCode()->handle();

        $s = $this->challengeRow();
        $this->assertSame('accepted', $s->delivery_status);
        $this->assertSame('<202611021200.123@smtp-relay.mailin.fr>', $s->provider_message_id);
        $this->assertNotNull($s->delivery_accepted_at);
        Http::assertSent(fn ($r) => $r['to'][0]['email'] === self::EMAIL
            && $r['sender']['email'] === 'noreply@csar.sn'
            && str_contains($r['htmlContent'], 'csar-logo.png')
            && preg_match('/\b\d{6}\b/', $r['textContent'])
            && $r->hasHeader('api-key'));
    }

    public function test_brevo_rejection_is_failed_with_safe_reason_and_no_secret_in_logs(): void
    {
        $this->useBrevo();
        Http::fake(['api.brevo.com/*' => Http::response(['code' => 'unauthorized', 'message' => 'Key not found: cle-de-test-fictive for '.self::EMAIL], 401)]);
        Log::spy();
        $this->dossier();
        $job = $this->requestCode();
        $job->handle();

        $s = $this->challengeRow();
        $this->assertSame('failed', $s->delivery_status);
        $this->assertSame('brevo:unauthorized:401', $s->delivery_error);
        Log::shouldHaveReceived('warning')->with('request_tracking_delivery_failed', \Mockery::on(function ($ctx) use ($job) {
            $dump = json_encode($ctx);
            return !str_contains($dump, $job->code) && !str_contains($dump, self::EMAIL) && !str_contains($dump, 'cle-de-test');
        }))->once();

        // Le visiteur voit l'échec, sans fausse promesse d'envoi.
        $this->getJson('/suivi/etat-envoi')->assertJson(['state' => 'failed']);
        $this->get('/suivi')->assertSee("L&#039;envoi du code a échoué", false)->assertSee('demander un nouveau code');
    }

    public function test_transient_provider_error_is_retried_not_marked_failed(): void
    {
        $this->useBrevo();
        Http::fake(['api.brevo.com/*' => Http::response(['code' => 'internal_error'], 503)]);
        $this->dossier();
        $this->requestCode()->handle();
        $s = $this->challengeRow();
        $this->assertSame('queued', $s->delivery_status);
        $this->assertSame('brevo:internal_error:503', $s->delivery_error);
    }

    public function test_missing_api_key_fails_immediately(): void
    {
        $this->useBrevo();
        config(['services.brevo.api_key' => '']);
        app('mail.manager')->forgetMailers();
        Http::fake();
        $this->dossier();
        $this->requestCode()->handle();
        $this->assertSame('failed', $this->challengeRow()->delivery_status);
        $this->assertSame('brevo:missing_api_key:0', $this->challengeRow()->delivery_error);
        Http::assertNothingSent();
    }

    public function test_wrong_email_is_skipped_and_shown_exactly_like_success(): void
    {
        Mail::fake();
        $this->dossier();
        $this->requestCode('autre@example.test')->handle();
        $this->assertSame('skipped', $this->challengeRow()->delivery_status);
        Mail::assertNothingSent();
        $wrong = $this->getJson('/suivi/etat-envoi')->json('state');

        $this->flushSession();
        $this->requestCode()->handle();
        $this->assertSame('accepted', $this->challengeRow()->delivery_status);
        $right = $this->getJson('/suivi/etat-envoi')->json('state');

        $this->assertSame('sent', $wrong);
        $this->assertSame($wrong, $right, 'Le visiteur ne doit pas pouvoir distinguer une adresse correcte.');
    }

    public function test_pending_state_and_session_binding(): void
    {
        $this->dossier();
        $this->requestCode();
        $this->getJson('/suivi/etat-envoi')->assertJson(['state' => 'pending']);
        $this->travel(2)->minutes();
        $this->getJson('/suivi/etat-envoi')->assertJson(['state' => 'delayed']);

        $this->flushSession();
        $this->getJson('/suivi/etat-envoi')->assertJson(['state' => null]);
    }

    public function test_pasted_reference_with_invisible_characters_is_found(): void
    {
        $this->dossier();
        $pasted = "\u{00A0}csar-otptest00001\u{200B} ";
        $this->from('/suivi')->post('/suivi', ['action' => 'lookup', 'tracking_code' => $pasted])
            ->assertSessionHas('tracking_verify')->assertSessionMissing('tracking_error');
    }

    public function test_visitor_can_correct_the_email_after_a_code_was_requested(): void
    {
        Mail::fake();
        $d = $this->dossier();
        $this->requestCode('faute@example.test')->handle();
        $this->get('/suivi')->assertSee("Corriger l'adresse ou demander un nouveau code", false)->assertSee('verification_code');

        $this->from('/suivi')->post('/suivi', ['action' => 'lookup', 'tracking_code' => $d->tracking_code])
            ->assertSessionHas('tracking_verify');
        $this->get('/suivi')->assertSee('name="email"', false)->assertDontSee('verification_code');

        $job = $this->requestCode();
        $job->handle();
        $this->from('/suivi')->post('/suivi', ['action' => 'confirm', 'verification_code' => $job->code])
            ->assertRedirect('/suivi?code='.$d->tracking_code);
    }

    public function test_issue_quota_message_after_three_codes(): void
    {
        $this->dossier();
        for ($i = 0; $i < 3; $i++) {
            $this->requestCode();
        }
        $this->from('/suivi')->post('/suivi', ['tracking_code' => 'CSAR-OTPTEST00001', 'email' => self::EMAIL])
            ->assertSessionHas('tracking_error', 'Trop de demandes de code pour cette référence. Réessayez dans une heure.');
        $this->assertDatabaseCount('request_tracking_challenges', 3);
    }

    public function test_full_flow_reference_email_code_status(): void
    {
        Mail::fake();
        $d = $this->dossier();
        $this->from('/suivi')->post('/suivi', ['action' => 'lookup', 'tracking_code' => $d->tracking_code])->assertSessionHas('tracking_verify');
        $job = $this->requestCode();
        $job->handle();
        $this->getJson('/suivi/etat-envoi')->assertJson(['state' => 'sent']);

        $this->from('/suivi')->post('/suivi', ['action' => 'confirm', 'verification_code' => $job->code])
            ->assertRedirect('/suivi?code='.$d->tracking_code);
        $this->get('/suivi?code='.$d->tracking_code)->assertOk()->assertSee('Objet fictif OTP')->assertSee('Demande reçue');
    }

    public function test_expired_code_is_not_sent_and_cannot_be_used(): void
    {
        Mail::fake();
        $this->dossier();
        $job = $this->requestCode();
        $this->travel(11)->minutes();
        $job->handle();
        Mail::assertNothingSent();
        $this->from('/suivi')->post('/suivi', ['action' => 'confirm', 'verification_code' => $job->code])
            ->assertSessionHas('tracking_error');
    }
}
