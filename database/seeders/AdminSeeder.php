<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $uuid = Str::uuid();
        $user = User::updateOrCreate(
            ['email' => 'admin@frontice.com'],
            [
                'id' => $uuid,
                'username' => 'admin',
                'password' => 'admin@frontice',
                'role' => 'admin',
                'email_verified_at' => now()
            ]
        );

        $admin = Admin::updateOrCreate(
            ['id' => $user->id],
            [
                'fullname' => 'Frontice Admin',
                'role' => 'root',
                'first_login' => false
            ]
        );
    }
}
