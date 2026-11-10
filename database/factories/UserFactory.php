<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'national_code' => fake()->unique()->numerify('085#######'),
            'mobile' => fake()->unique()->numerify('0915#######'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'customer',
            'policyholder_type' => 'حقیقی',
            'payment_badge' => 'none',
            'preferred_notification_hour' => 10,
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    public function manager(array $permissions = []): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'manager',
            'permissions' => $permissions,
        ]);
    }
}
