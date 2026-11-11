<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Insurer;
use App\Models\Policy;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Policy>
 */
class PolicyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'            => User::factory(),
            'insurer_id'         => Insurer::factory(),
            'policy_number'      => '1405/3116/' . $this->faker->unique()->numerify('######'),
            'title'              => 'ثالث - پژو 206',
            'insurance_category' => 'ثالث',
            'vehicle_name'       => 'پژو 206',
            'total_amount'       => 120000000,
            'initial_payment'    => 20000000,
            'paid_amount'        => 20000000,
            'payment_type'       => 'installment',
            'installments_count' => 5,
            'issued_at'          => now('Asia/Tehran')->toDateString(),
            'expires_at'         => now('Asia/Tehran')->addYear()->toDateString(),
        ];
    }
}