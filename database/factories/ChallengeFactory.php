<?php

namespace Database\Factories;

use App\Models\Challenge;
use App\Models\Admin;
use App\Models\Level;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ChallengeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Challenge::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => $this->faker->uuid,
            'admin_id' => Admin::inRandomOrder()->first()->id,
            'level_id' => 1,
            'title' => $this->faker->sentence(3),
            'image' => $this->faker->imageUrl(640, 480, 'tech'),
            'technical' => json_encode([
                'technical' => ['HTML', 'CSS']
            ]),
            'source' => "challenges/d5b9bef6-43b1-4e43-a23e-4f643f3a92d3/premium-12.zip", // URL nguồn
            'figma' => "challenges/8ce19722-42ff-460d-8779-c1d6dd47717a/qr-code.zip",
            'point' => $this->faker->numberBetween(10, 25),
            'short_des' => $this->faker->text(100),
            'desc' => json_encode([
                'time' => now()->timestamp,
                'blocks' => [
                    [
                        'type' => 'header',
                        'data' => [
                            'text' => $this->faker->sentence(6),
                            'level' => 2
                        ]
                    ],
                    [
                        'type' => 'paragraph',
                        'data' => [
                            'text' => $this->faker->paragraph(4)
                        ]
                    ]
                ],
                'version' => '2.19.0'
            ]),
            'premium' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
