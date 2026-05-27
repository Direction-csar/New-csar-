<?php

namespace Tests\Feature;

use App\Models\Donation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonationTest extends TestCase
{
    use RefreshDatabase;

    public function test_donation_page_returns_200(): void
    {
        $response = $this->get('/fr/faire-un-don');
        $response->assertStatus(200);
        $response->assertViewIs('public.donations.index');
    }

    public function test_donation_process_validates_required_fields(): void
    {
        $response = $this->postJson('/fr/faire-un-don/process', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['full_name', 'email', 'amount', 'payment_method', 'payment_provider', 'donation_type']);
    }

    public function test_donation_process_rejects_invalid_amount(): void
    {
        $response = $this->postJson('/fr/faire-un-don/process', [
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'amount' => 100, // Under minimum of 500
            'payment_method' => 'wave',
            'payment_provider' => 'paydunya',
            'donation_type' => 'single',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount']);
    }

    public function test_donation_process_rejects_invalid_payment_method(): void
    {
        $response = $this->postJson('/fr/faire-un-don/process', [
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'amount' => 5000,
            'payment_method' => 'bitcoin', // Invalid
            'payment_provider' => 'paydunya',
            'donation_type' => 'single',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['payment_method']);
    }

    public function test_donation_cancel_page_returns_200(): void
    {
        $response = $this->get('/fr/faire-un-don/cancel');
        $response->assertStatus(200);
    }

    public function test_donation_model_scopes(): void
    {
        Donation::factory()->create(['payment_status' => 'success']);
        Donation::factory()->create(['payment_status' => 'pending']);
        Donation::factory()->create(['payment_status' => 'failed']);

        $this->assertEquals(1, Donation::successful()->count());
        $this->assertEquals(1, Donation::pending()->count());
        $this->assertEquals(1, Donation::failed()->count());
    }

    public function test_donation_formatted_amount(): void
    {
        $donation = new Donation(['amount' => 25000, 'currency' => 'XOF']);
        $this->assertStringContainsString('25', $donation->formatted_amount);
        $this->assertStringContainsString('XOF', $donation->formatted_amount);
    }
}
