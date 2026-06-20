<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 
        'name', 
        'description', 
        'image_path', 
        'is_retail', 
        'retail_price', 
        'is_wholesale', 
        'wholesale_unit_kg', 
        'wholesale_price'
    ];

    protected $casts = [
        'is_retail' => 'boolean',
        'is_wholesale' => 'boolean',
        'retail_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
    ];

    public function category() 
    { 
        return $this->belongsTo(Category::class); 
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Проверка: есть ли активные заказы с этим товаром
     */
    public function hasActiveOrders(): bool
    {
        return OrderItem::where('product_id', $this->id)
            ->whereHas('order', function ($query) {
                $query->whereNotIn('status', [
                    Order::STATUS_DELIVERED, 
                    Order::STATUS_CANCELLED
                ]);
            })
            ->exists();
    }

    /**
     * Безопасное удаление: soft delete или hard delete
     */
    public function safeDelete(): bool
    {
        if ($this->hasActiveOrders()) {
            // Есть активные заказы — только помечаем удалённым
            return $this->delete(); // soft delete
        }

        // Нет активных заказов — удаляем полностью
        // Сначала удаляем связанные order_items (они уже не нужны)
        $this->orderItems()->delete();
        
        // Удаляем файл изображения
        if ($this->image_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($this->image_path);
        }
        
        return (bool) $this->forceDelete();
    }
}