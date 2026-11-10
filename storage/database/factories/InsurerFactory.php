<?php

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
            'name' => 'بیمه ایران - '.fake()->unique()->numerify('###'),
        ];
    }
}
