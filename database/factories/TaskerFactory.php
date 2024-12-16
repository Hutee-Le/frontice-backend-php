<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tasker>
 */
class TaskerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' =>  User::factory()->state([
                'role' => 'tasker',
            ])->create()->id,
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'company' => $this->faker->company,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
