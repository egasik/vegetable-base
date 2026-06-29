<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Order extends Model
{
    use SoftDeletes;

    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_SHIPPING = 'shipping';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    const ACTIVE_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PAID,
        self::STATUS_SHIPPING,
    ];

    const STATUS_TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_PAID, self::STATUS_CANCELLED],
        self::STATUS_PAID => [self::STATUS_SHIPPING, self::STATUS_CANCELLED],
        self::STATUS_SHIPPING => [self::STATUS_DELIVERED, self::STATUS_CANCELLED],
        self::STATUS_DELIVERED => [],
        self::STATUS_CANCELLED => [],
    ];

    const REVERT_WINDOW_MINUTES = 10;

    protected $fillable = [
        'user_id', 
        'total_amount', 
        'status', 
        'payment_method', 
        'payment_id', 
        'paid_at',
        'status_changed_at',
        'previous_status',
        'deleted_by',
        'delete_reason',
        'delivery_address',
        'delivery_city',
        'delivery_region',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'status_changed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at', 'desc');
    }

    public function deletedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function canTransitionTo(string $newStatus): bool
{
    // Если текущий статус совпадает с новым — нет смысла менять
    if ($this->status === $newStatus) {
        return false;
    }

    // Если можно откатить и выбираем предыдущий статус — разрешаем
    if ($this->canRevert() && $newStatus === $this->previous_status) {
        return true;
    }

    // Стандартная проверка по таблице переходов
    $allowedTransitions = self::STATUS_TRANSITIONS[$this->status] ?? [];
    return in_array($newStatus, $allowedTransitions);
}

public function getAllowedNextStatuses(): array
{
    $allowed = self::STATUS_TRANSITIONS[$this->status] ?? [];
    
    // Добавляем предыдущий статус только если он отличается от текущего
    if ($this->canRevert() 
        && $this->previous_status 
        && $this->previous_status !== $this->status
        && !in_array($this->previous_status, $allowed)) {
        array_unshift($allowed, $this->previous_status);
    }
    
    return $allowed;
}


    public function canRevert(): bool
{
    if (!$this->status_changed_at || !$this->previous_status) {
        return false;
    }

    // Если предыдущий статус совпадает с текущим — откатывать некуда
    if ($this->previous_status === $this->status) {
        return false;
    }

    // Вручённый заказ нельзя откатить
    if ($this->status === self::STATUS_DELIVERED) {
        return false;
    }

    $minutesSinceChange = Carbon::parse($this->status_changed_at)->diffInMinutes(now());
    return $minutesSinceChange < self::REVERT_WINDOW_MINUTES;
}

    public function getRevertMinutesLeft(): int
    {
        if (!$this->canRevert()) {
            return 0;
        }

        $minutesSinceChange = Carbon::parse($this->status_changed_at)->diffInMinutes(now());
        return max(0, self::REVERT_WINDOW_MINUTES - $minutesSinceChange);
    }

    public function getNextStatusLabel(): ?string
    {
        $allowed = $this->getAllowedNextStatuses();
        
        if (empty($allowed)) {
            return null;
        }

        $expected = array_diff($allowed, [self::STATUS_CANCELLED, $this->previous_status]);
        
        if (empty($expected)) {
            return null;
        }

        return match(reset($expected)) {
            self::STATUS_PAID => 'Ожидается оплата',
            self::STATUS_SHIPPING => 'Ожидается отправка',
            self::STATUS_DELIVERED => 'Ожидается вручение',
            default => null,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Ожидает оплаты',
            self::STATUS_PAID => 'Оплачен',
            self::STATUS_SHIPPING => 'В доставке',
            self::STATUS_DELIVERED => 'Вручен',
            self::STATUS_CANCELLED => 'Отменен',
            default => 'Неизвестно',
        };
    }

    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES);
    }
    public function getFullDeliveryAddressAttribute(): string
{
    $parts = array_filter([
        $this->delivery_city,
        $this->delivery_region,
        $this->delivery_address,
    ]);
    
    return implode(', ', $parts);
}
public function deliveryPhotos()
{
    return $this->hasMany(DeliveryPhoto::class);
}
/**
 * Получить текстовую метку для значения статуса
 */
public function getStatusLabelForValue(string $status): string
{
    return match($status) {
        self::STATUS_PENDING => 'Ожидает оплаты',
        self::STATUS_PAID => 'Оплачен',
        self::STATUS_SHIPPING => 'В доставке',
        self::STATUS_DELIVERED => 'Вручен',
        self::STATUS_CANCELLED => 'Отменен',
        default => 'Неизвестно',
    };
}
}