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
                'name' => 'Главный Администратор',
                'password' => Hash::make('admin123'), // Пароль для входа
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}