<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Challenge;

class ChallengeSeeder extends Seeder
{
    public function run()
    {
        Challenge::factory()->count(20)->create(); // Tạo 20 Challenge
    }
}
