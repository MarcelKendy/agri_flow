<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

class UserFactory extends Factory
{
    protected $model = \App\Models\User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'cpf' => $this->faker->numerify('###########'), // Generate a random 11-digit CPF
            'email' => $this->faker->unique()->safeEmail,
            'sp' => $this->faker->numberBetween(0, 1),
            'group_id' => null, // You can customize this as needed
            'photo' => $this->faker->imageUrl(100, 100), // Generate a placeholder image URL
            'password' => bcrypt('password'), // Default password (you can change this)
            'level' => 0,
            'status' => 1,
        ];
    }
}
