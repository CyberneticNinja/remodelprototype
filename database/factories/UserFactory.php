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
            'type'               => 'contractor',
            'first_name'         => fake()->firstName(),
            'last_name'          => fake()->lastName(),
            'email'              => fake()->unique()->safeEmail(),
            'phone'              => fake()->phoneNumber(),
            'company_name'       => fake()->company(),
            'company_address'    => fake()->address(),
            'company_phone'      => fake()->phoneNumber(),
            'email_verified_at'  => now(),
            'password'           => static::$password ??= Hash::make('password'),
            'remember_token'     => Str::random(10),
        ];
    }

    public function client(): static
    {
        return $this->state(fn (array $attributes) => [
            'type'            => 'client',
            'company_name'    => null,
            'company_address' => null,
            'company_phone'   => null,
            'address'         => fake()->address(),
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
