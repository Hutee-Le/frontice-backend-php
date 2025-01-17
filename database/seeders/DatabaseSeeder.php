<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\AdminSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(LevelSeeder::class);
        $this->call(AdminSeeder::class);
        $this->call(ServiceSeeder::class);
        // $this->call([
        //     TaskeeSeeder::class,           // Tạo 10 Taskee
        //     TaskerSeeder::class,        // Tạo 10 Tasker
        //     ChallengeSeeder::class,        // Tạo 20 Challenge
        //     ChallengeSolutionSeeder::class, // Tạo 30 Challenge Solutions
        //     TaskSeeder::class,           // Tạo 100 Task
        // ]);
    }
}
