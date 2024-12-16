<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Taskee;

class TaskeeSeeder extends Seeder
{
    public function run()
    {
        Taskee::factory()->count(10)->create(); // Tạo 10 Taskee
    }
}
