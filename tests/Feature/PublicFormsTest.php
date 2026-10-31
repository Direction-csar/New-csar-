<?php

namespace Tests\Feature;

use App\Models\NewsletterSubscriber;
use App\Models\PublicRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PublicFormsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Queue::fake();
    }

    private function payload(array $changes = []): array
    {
        return array_replace([
            'nom' => 'Fictif', 'prenom' => 'Demandeur', 'email' => 'demandeur@example.test',
            'telephone' => '77 123 45 67', 'type_demande' => 'aide_alimentaire',
            'objet' => 'Assistance fictive', 'description' => 'Description fictive pour audit.',
            'region' => 'Dakar', 'latitude' => 14.7167, 'longitude' => -17.4677,
        ], $changes);
    }

    public function test_can_submit_request(): void
    {
        $this->post('/fr/demande', $this->payload())->assertRedirect()->assertSessionHas('tracking_code');
        $d = PublicRequest::sole();
        $this->assertSame('soumise', $d->workflow_status);
        $this->assertStringStartsWith('CSAR-', $d->tracking_code);
        $this->assertSame('+221771234567', $d->phone);
    }

    public function test_each_allowed_request_type_including_optional_addresses(): void
    {
        foreach (['aide_alimentaire', 'demande_audience', 'information_generale', 'autre'] as $type) {
            $this->postJson('/demande', $this->payload([
                'prenom' => $type, 'type_demande' => $type, 'type_demande_autre' => 'Sujet spécifique',
                'region' => $type === 'aide_alimentaire' ? 'Dakar' : null, 'adresse' => null,
            ]))->assertOk()->assertJsonPath('success', true);
        }
        $this->assertDatabaseCount('public_requests', 4);
        $this->assertDatabaseHas('public_requests', ['type' => 'information_generale', 'address' => '', 'region' => '']);
        $this->postJson('/demande', $this->payload(['type_demande' => 'type_invente']))->assertUnprocessable();
    }

    public function test_request_requires_mandatory_fields(): void
    {
        $this->post('/fr/demande', ['nom' => 'Test'])
            ->assertSessionHasErrors(['prenom', 'email', 'telephone', 'type_demande', 'objet', 'description']);
    }

    public function test_can_submit_contact_message(): void
    {
        $this->post('/fr/contact', [
            'name' => 'Contact fictif', 'email' => 'contact@example.test', 'phone' => '+221771234567',
            'subject' => 'Question générale', 'message' => 'Message de contact fictif.',
        ])->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('contact_messages', ['email' => 'contact@example.test', 'subject' => 'Question générale']);
    }

    public function test_can_subscribe_to_newsletter(): void
    {
        $this->postJson('/fr/newsletter/subscribe', ['email' => 'newsletter@example.test'])->assertOk();
        $this->assertDatabaseHas('newsletter_subscribers', ['email' => 'newsletter@example.test', 'status' => 'pending']);
    }

    public function test_can_unsubscribe_from_newsletter(): void
    {
        $subscriber = NewsletterSubscriber::create(['email' => 'unsubscribe@example.test', 'status' => 'active', 'subscribed_at' => now()]);
        $this->postJson('/fr/newsletter/unsubscribe', ['email' => $subscriber->email])->assertOk();
        $this->assertSame('unsubscribed', $subscriber->fresh()->status);
    }

    public function test_duplicate_request_prevention_never_reveals_reference(): void
    {
        $this->postJson('/demande', $this->payload())->assertOk();
        $first = PublicRequest::sole();
        $this->postJson('/fr/demande', $this->payload(['email' => 'thirdparty@example.test', 'telephone' => '00221 77 123 45 67']))
            ->assertStatus(409)->assertDontSee($first->tracking_code)->assertDontSee($first->email);
        $this->assertDatabaseCount('public_requests', 1);
    }

    public function test_rate_limiting_on_tracking_form(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post('/suivi', ['tracking_code' => 'CSAR-FICTIF', 'email' => 'fictif@example.test'])->assertRedirect();
        }
        $this->post('/fr/suivre-ma-demande', ['tracking_code' => 'CSAR-FICTIF', 'email' => 'fictif@example.test'])->assertStatus(429);
    }

    public function test_email_validation_on_forms(): void
    {
        $this->post('/fr/contact', ['name' => 'Test', 'email' => 'invalid', 'subject' => 'Test', 'message' => 'Fictif'])
            ->assertSessionHasErrors('email');
        $this->postJson('/demande', $this->payload(['email' => 'invalid']))->assertUnprocessable();
    }

    public function test_csrf_protection_on_forms(): void
    {
        // Disable only the testing bypass, keep the real CSRF middleware.
        $this->app->instance('env', 'local');
        $this->post('/suivi', ['tracking_code' => 'CSAR-FICTIF', 'email' => 'test@example.test'])->assertStatus(419);
    }

    public function test_unimplemented_public_attachments_are_rejected_instead_of_silently_lost(): void
    {
        $this->postJson('/demande', $this->payload(['attachment' => UploadedFile::fake()->create('document.pdf', 10)]))
            ->assertUnprocessable()->assertJsonValidationErrors('attachment');
        $this->assertDatabaseCount('public_requests', 0);
    }
}
