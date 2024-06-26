<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'), 
            'role' => 'patient', 
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): UserFactory{

        return $this->state(function (array $attributes) {
            return [
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'password' => bcrypt('Admin@123'),
                'role' => 'admin',
            ];
        });
    }

    public function doctor(): UserFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'doctor', // assign doctor role
            ];
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
