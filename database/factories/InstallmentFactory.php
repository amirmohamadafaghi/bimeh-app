<?php

declare(strict_types=1);

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
            'policy_id'          => Policy::factory(),
            'installment_number' => 1,
            'amount'             => 20000000,
            'paid_amount'        => 0,
            'due_date'           => now('Asia/Tehran')->addDays(10)->toDateString(),
            'due_date_jalali'    => '1405/08/25',
            'status'             => 'pending',
            'payment_type'       => 'قسط',
        ];
    }
}