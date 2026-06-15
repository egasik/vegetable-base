<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// 1. Добавили 'role', 'avatar_path', 'card_data' в Fillable
#[Fillable(['name', 'email', 'password', 'role', 'avatar_path', 'card_data'])]
// 2. Добавили 'card_data' в Hidden, чтобы данные карты не утекли в API/JSON
#[Hidden(['password', 'remember_token', 'card_data'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // Автоматическое хеширование Bcrypt при записи
        ];
    }

    // --- СВЯЗИ С ДРУГИМИ МОДЕЛЯМИ (Добавлено ниже) ---

    /**
     * Связь: У пользователя может быть много заказов (1 ко многим)
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Связь: Пользователь может быть автором многих статей (1 ко многим)
     */
    public function articles()
    {
        return $this->hasMany(Article::class, 'author_id');
    }
}