<?php

namespace Database\Factories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => $this->faker->uuid, // Tạo UUID cho id
            'username' => $this->faker->unique()->userName,
            'email' => $this->faker->unique()->safeEmail,
            'email_verified_at' => Carbon::now(),
            'password' => bcrypt('password'), // Mã hóa mật khẩu với giá trị mặc định
            'role' => $this->faker->randomElement(['tasker', 'taskee']),
            'image' => $this->faker->optional()->imageUrl(640, 480, 'people'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
