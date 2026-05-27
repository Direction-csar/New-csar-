<?php

namespace Tests\Feature;

use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_phone_number_normalization(): void
    {
        $service = new SmsService();

        // Use reflection to test private method
        $method = new \ReflectionMethod(SmsService::class, 'normalizePhoneNumber');
        $method->setAccessible(true);

        // Sénégal number without prefix
        $this->assertEquals('+221771234567', $method->invoke($service, '771234567'));

        // With 0 prefix
        $this->assertEquals('+221771234567', $method->invoke($service, '0771234567'));

        // Already with +221
        $this->assertEquals('+221771234567', $method->invoke($service, '+221771234567'));

        // With 221 but no +
        $this->assertEquals('+221771234567', $method->invoke($service, '221771234567'));

        // With spaces and dashes
        $this->assertEquals('+221771234567', $method->invoke($service, '77 123 45 67'));
    }

    public function test_amount_validation_in_paydunya(): void
    {
        $service = app(\App\Services\PayDunyaService::class);

        // Valid amount
        $result = $service->validateAmount(5000);
        $this->assertTrue($result['valid']);

        // Too low
        $result = $service->validateAmount(100);
        $this->assertFalse($result['valid']);

        // Too high
        $result = $service->validateAmount(5000000);
        $this->assertFalse($result['valid']);

        // Non-numeric
        $result = $service->validateAmount('abc');
        $this->assertFalse($result['valid']);
    }

    public function test_paydunya_payment_methods_returns_array(): void
    {
        $service = app(\App\Services\PayDunyaService::class);
        $methods = $service->getPaymentMethods();

        $this->assertIsArray($methods);
        $this->assertArrayHasKey('wave', $methods);
        $this->assertArrayHasKey('orange_money', $methods);
        $this->assertArrayHasKey('credit_card', $methods);
    }

    public function test_suggested_amounts_returns_array(): void
    {
        $service = app(\App\Services\PayDunyaService::class);
        $amounts = $service->getSuggestedAmounts();

        $this->assertIsArray($amounts);
        $this->assertNotEmpty($amounts);
        $this->assertContains(5000, $amounts);
    }
}
