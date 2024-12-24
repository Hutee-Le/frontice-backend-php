<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChallengeSolution;
use App\Models\Challenge;
use App\Models\Taskee;

class ChallengeSolutionSeeder extends Seeder
{
    public function run()
    {
        // Lấy tất cả Taskee và Challenge từ cơ sở dữ liệu
        $taskees = Taskee::all();
        $challenges = Challenge::all();

        if ($taskees->count() < 10 || $challenges->count() < 20) {
            throw new \Exception("Not enough Taskees or Challenges created.");
        }

        // Tạo 15 Challenge Solutions
        for ($i = 0; $i < 5; $i++) {
            $taskee = $taskees->random();
            $challenge = $challenges->random();

            // Kiểm tra xem cặp taskee_id và challenge_id đã tồn tại chưa
            if (!ChallengeSolution::where('taskee_id', $taskee->id)->where('challenge_id', $challenge->id)->exists()) {
                ChallengeSolution::factory()->create([
                    'taskee_id' => $taskee->id, // Lấy ngẫu nhiên Taskee
                    'challenge_id' => $challenge->id, // Lấy ngẫu nhiên Challenge
                ]);
            }
        }
    }
}
