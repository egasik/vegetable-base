<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
                User::updateOrCreate(
            ['email' => 'admin@vegetable.ru'],
            [
                'name' => 'Админ',
                'last_name' => 'Системы',
                'middle_name' => '',
                'phone' => '+7-(999)-000-00-00',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}