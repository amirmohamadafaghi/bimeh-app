<?php

namespace Database\Factories;

use App\Models\Installment;
use App\Models\Policy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Installment>
 */
class InstallmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'policy_id' => Policy::factory(),
            'installment_number' => 1,
            'amount' => 15000000,
            'paid_amount' => 0,
            'payment_identifier' => fake()->numerify('3145#############'),
            'due_date' => now('Asia/Tehran')->addDays(10)->toDateString(),
            'status' => 'pending',
            'payment_type' => 'قسط',
        ];
    }
}
