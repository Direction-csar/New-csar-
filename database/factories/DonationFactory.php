<?php

namespace Database\Factories;

use App\Models\Donation;
use Illuminate\Database\Eloquent\Factories\Factory;

class DonationFactory extends Factory
{
    protected $model = Donation::class;

    public function definition(): array
    {
        return [
            'full_name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'phone' => '+221' . $this->faker->numerify('#########'),
            'amount' => $this->faker->randomElement([1000, 2500, 5000, 10000, 25000]),
            'payment_method' => $this->faker->randomElement(['wave', 'orange_money', 'credit_card']),
            'payment_provider' => 'paydunya',
            'payment_status' => 'pending',
            'currency' => 'XOF',
            'donation_type' => 'single',
            'is_anonymous' => false,
        ];
    }

    public function successful(): static
    {
        return $this->state(['payment_status' => 'success', 'processed_at' => now()]);
    }

    public function failed(): static
    {
        return $this->state(['payment_status' => 'failed', 'failed_at' => now(), 'failure_reason' => 'Payment declined']);
    }
}
