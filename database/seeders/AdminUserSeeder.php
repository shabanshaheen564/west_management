<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@waste.local'],
            [
                'name'      => 'System Administrator',
                'password'  => bcrypt('Admin@123456'),
                'is_active' => true,
                'locale'    => 'en',
            ]
        );
        $admin->assignRole('admin');

        $supervisor = User::firstOrCreate(
            ['email' => 'supervisor@waste.local'],
            [
                'name'      => 'Ahmed Al-Khalidi',
                'password'  => bcrypt('Super@123'),
                'is_active' => true,
                'locale'    => 'ar',
            ]
        );
        $supervisor->assignRole('supervisor');

        $user = User::firstOrCreate(
            ['email' => 'user@waste.local'],
            [
                'name'      => 'Mohamed Hassan',
                'password'  => bcrypt('User@123'),
                'is_active' => true,
                'locale'    => 'ar',
            ]
        );
        $user->assignRole('user');
    }
}