<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Service::updateOrCreate([
            'name' => 'Gold Monthly',
            'type' => 'monthly',
            'price' => 59000,
        ]);
        Service::updateOrCreate([
            'name' => 'Gold 3-Monthly',
            'type' => '3-monthly',
            'price' => 168000,
        ]);
        Service::updateOrCreate([
            'name' => 'Gold 6-Monthly',
            'type' => '6-monthly',
            'price' => 318000,
        ]);
        Service::updateOrCreate([
            'name' => 'Gold Yearly',
            'type' => 'yearly',
            'price' => 609000,
        ]);
    }
}
