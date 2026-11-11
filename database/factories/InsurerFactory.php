<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Insurer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Insurer>
 */
class InsurerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'         => 'بیمه ایران - ' . $this->faker->unique()->numerify('###'),
            'code'         => '3116',
            'service_type' => 'ثالث و بدنه',
            'is_active'    => true,
        ];
    }
}