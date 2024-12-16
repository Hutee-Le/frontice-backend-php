<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Level::updateOrCreate([
            'name' => 'Newbie',
            'default_point' => 0,
            'required_point' => 0,
        ]);
        Level::updateOrCreate([
            'name' => 'Bronze',
            'default_point' => 50,
            'required_point' => 100,
        ]);
        Level::updateOrCreate([
            'name' => 'Silver',
            'default_point' => 100,
            'required_point' => 150,
        ]);
        Level::updateOrCreate([
            'name' => 'Gold',
            'default_point' => 150,
            'required_point' => 450,
        ]);
        Level::updateOrCreate([
            'name' => 'Diamond',
            'default_point' => 200,
            'required_point' => 1050,
        ]);
    }
}
