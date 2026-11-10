<?php

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
            'user_id' => User::factory(),
            'insurer_id' => Insurer::factory(),
            'policy_number' => '1405/314/60020/'.fake()->unique()->numerify('####'),
            'title' => 'ثالث - سواری پژو 405',
            'insurance_category' => 'ثالث',
            'vehicle_name' => 'سواری پژو 405',
            'plate_number' => '82ط867 ایران52',
            'vehicle_model' => '1398',
            'total_amount' => 60000000,
            'initial_payment' => 20000000,
            'issued_at' => now('Asia/Tehran')->subMonth()->toDateString(),
            'expires_at' => now('Asia/Tehran')->addMonths(11)->toDateString(),
            'status' => 'active',
        ];
    }
}
