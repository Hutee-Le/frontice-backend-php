<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\Tasker; // Đảm bảo bạn import model Tasker nếu cần
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class; // Đặt model tương ứng

    public function definition()
    {
        return [
            'id' => $this->faker->uuid(), // Tạo UUID cho id
            'tasker_id' => Tasker::inRandomOrder()->first()->id, // Tạo Tasker mới hoặc có thể dùng một ID có sẵn
            'title' => $this->faker->sentence(5), // Tiêu đề ngẫu nhiên
            'image' => $this->faker->imageUrl(640, 480, 'business'), // URL hình ảnh
            'technical' => json_encode([
                'technical' => ['HTML', 'CSS']
            ]), // JSON kỹ thuật
            'source' => "challenges/d5b9bef6-43b1-4e43-a23e-4f643f3a92d3/premium-12.zip", // URL nguồn
            'figma' => "challenges/8ce19722-42ff-460d-8779-c1d6dd47717a/qr-code.zip", // Figma có thể null
            'required_point' => $this->faker->numberBetween(1, 100), // Điểm yêu cầu
            'short_des' => $this->faker->sentence(10), // Mô tả ngắn
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
            ]), // JSON mô tả
            'expired' => $this->faker->dateTimeBetween('now', '+1 year'), // Thời gian hết hạn
            'status' => $this->faker->randomElement(['pending', 'valid', 'deleted']), // Trạng thái
            'is_skip' => $this->faker->boolean(20), // Có thể skip với 20% xác suất
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
