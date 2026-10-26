<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'quantity',
        'price_at_moment',
        'purchase_type',
        'unit',
        'unit_weight_kg',
        'line_total',
    ];

    protected $casts = [
        'price_at_moment' => 'decimal:2',
        'quantity' => 'decimal:3',
        'unit_weight_kg' => 'decimal:3',
        'line_total' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
}
