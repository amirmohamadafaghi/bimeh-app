<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password = null;

    public function definition(): array
    {
        $mobile = '0912' . $this->faker->unique()->numerify('#######');

        return [
            'name'              => $this->faker->name(),
            'national_code'     => $this->faker->unique()->numerify('##########'),
            'mobile'            => $mobile,
            'policyholder_type' => 'حقیقی',
            'password'          => static::$password ??= Hash::make('password'),
            'role'              => 'customer',
            'payment_badge'     => 'normal',
        ];
    }
}