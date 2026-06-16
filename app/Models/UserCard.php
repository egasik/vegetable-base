<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCard extends Model
{
    // Разрешаем массовое заполнение
    protected $fillable = ['user_id', 'card_number', 'cvc_code', 'pin_code', 'is_default'];

    // КРИТИЧЕСКИ ВАЖНО: Никогда не отдавать эти данные в JSON/API даже админу
    protected $hidden = ['card_number', 'cvc_code', 'pin_code'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}