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
                'name' => 'Иван',
                'last_name' => 'Иванов',
                'middle_name' => 'Иванович',
                'phone' => '+7-(912)-345-67-89',
                'password' => Hash::make('user123'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );
    }
}