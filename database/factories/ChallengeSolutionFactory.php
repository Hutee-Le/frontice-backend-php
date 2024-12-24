<?php

namespace Database\Factories;

use App\Models\ChallengeSolution;
use App\Models\Challenge;
use App\Models\Taskee;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChallengeSolutionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ChallengeSolution::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => $this->faker->uuid, // Tạo UUID cho id
            'challenge_id' => Challenge::inRandomOrder()->first()->id, // Lấy ngẫu nhiên id từ bảng challenges
            'taskee_id' => Taskee::inRandomOrder()->first()->id, // Lấy ngẫu nhiên id từ bảng taskees
            'admin_id' => $this->faker->optional()->randomElement(Admin::pluck('id')->toArray()), // Lấy ngẫu nhiên admin hoặc để null
            'title' => $this->faker->optional()->sentence(3), // Tiêu đề ngẫu nhiên
            'github' => $this->faker->optional()->url, // Link GitHub của giải pháp
            'live_github' => $this->faker->optional()->url, // Link live trên GitHub
            'pride_of' => $this->faker->optional()->paragraph, // Đoạn văn tự hào về giải pháp
            'challenge_overcome' => $this->faker->optional()->paragraph, // Những thách thức đã vượt qua
            'help_with' => $this->faker->optional()->paragraph, // Những gì cần hỗ trợ
            'status' => $this->faker->randomElement(['pointed', 'deleted']), // Trạng thái ngẫu nhiên
            'submitted_at' => $this->faker->optional()->dateTimeThisYear, // Thời gian nộp bài
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
