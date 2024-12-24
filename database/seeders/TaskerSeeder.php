<?php

namespace Database\Seeders;

use App\Models\Tasker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tasker::factory()->count(10)->create();
    }
}
