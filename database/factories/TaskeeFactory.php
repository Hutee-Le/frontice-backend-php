<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Taskee;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskeeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Taskee::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' =>  User::factory()->state([
                'role' => 'taskee',
            ])->create()->id,
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'github' => $this->faker->optional()->url,
            'bio' => $this->faker->optional()->paragraph,
            'cv' => $this->faker->optional()->url,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
