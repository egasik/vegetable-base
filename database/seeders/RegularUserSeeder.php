<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class RegularUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'user@vegetable.ru'],
            [
                'name' => 'Тестовый Покупатель',
                'password' => Hash::make('user123'), // Пароль для входа
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );
    }
}