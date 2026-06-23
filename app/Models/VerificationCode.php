<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationCode extends Model
{
    const CODE_LIFETIME_MINUTES = 10;
    const MAX_ATTEMPTS = 5;

    const PURPOSES = [
        'email_confirm' => 'Подтверждение email при регистрации',
        'email_change' => 'Смена email',
        'password_change' => 'Смена пароля',
    ];

    protected $fillable = [
        'user_id',
        'code',
        'purpose',
        'new_value',
        'attempts',
        'expires_at',
        'confirmed',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'confirmed' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return now()->greaterThan($this->expires_at);
    }

    public function canAttempt(): bool
    {
        return !$this->isExpired() && $this->attempts < self::MAX_ATTEMPTS;
    }

    public function getMinutesLeft(): int
    {
        if ($this->isExpired()) return 0;
        return max(0, now()->diffInMinutes($this->expires_at, false));
    }

    public static function generateCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public static function createForUser(int $userId, string $purpose, ?string $newValue = null): self
    {
        // Удаляем старые неподтверждённые коды этого типа
        self::where('user_id', $userId)
            ->where('purpose', $purpose)
            ->where('confirmed', false)
            ->delete();

        return self::create([
            'user_id' => $userId,
            'code' => self::generateCode(),
            'purpose' => $purpose,
            'new_value' => $newValue,
            'expires_at' => now()->addMinutes(self::CODE_LIFETIME_MINUTES),
        ]);
    }

    public static function getActiveCode(int $userId, string $purpose): ?self
    {
        return self::where('user_id', $userId)
            ->where('purpose', $purpose)
            ->where('confirmed', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }
}