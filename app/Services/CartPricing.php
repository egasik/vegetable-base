<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Validation\ValidationException;

class CartPricing
{
    public function line(Product $product, string $type, mixed $quantity): array
    {
        $retail = $type === 'retail';
        if ($product->trashed() || ! in_array($type, ['retail', 'wholesale'], true)
            || ($retail ? ! $product->is_retail : (! $product->is_wholesale || $product->wholesale_unit_kg <= 0))) {
            throw ValidationException::withMessages(['cart' => 'Этот вариант покупки больше недоступен.']);
        }
        $value = filter_var($quantity, FILTER_VALIDATE_FLOAT);
        $step = $retail ? 0.5 : 1;
        if ($value === false || ! is_finite($value) || $value < $step || $value > 9999
            || abs($value / $step - round($value / $step)) > 0.000001) {
            throw ValidationException::withMessages(['quantity' => $retail
                ? 'Укажите вес от 0,5 кг с шагом 0,5 кг (не более 9999 кг).'
                : 'Укажите целое количество упаковок от 1 до 9999.']);
        }
        $kopecks = (int) round((float) ($retail ? $product->retail_price : $product->wholesale_price) * 100);
        if ($kopecks <= 0) {
            throw ValidationException::withMessages(['cart' => 'Для товара не установлена цена.']);
        }
        $subtotal = (int) round($kopecks * $value, 0, PHP_ROUND_HALF_UP);
        $unitWeight = $retail ? 1 : (float) $product->wholesale_unit_kg;

        return ['product' => $product, 'product_id' => $product->id, 'product_name' => $product->name,
            'purchase_type' => $type, 'quantity' => $value, 'price' => $kopecks / 100,
            'unit' => $retail ? 'кг' : 'уп.', 'unit_weight_kg' => $unitWeight,
            'weight' => $value * $unitWeight, 'subtotal' => $subtotal / 100, 'subtotal_kopecks' => $subtotal];
    }

    public function quote(array $cart, bool $lock = false): array
    {
        $query = Product::whereIn('id', array_column($cart, 'product_id'))->orderBy('id');
        if ($lock) {
            $query->lockForUpdate();
        }
        $products = $query->get()->keyBy('id');
        $items = [];
        $total = 0;
        $weight = 0;
        foreach ($cart as $key => $entry) {
            $product = $products->get($entry['product_id']);
            if (! $product) {
                throw ValidationException::withMessages(['cart' => 'Товар больше недоступен. Обновите корзину.']);
            }
            $line = $this->line($product, $entry['purchase_type'], $entry['quantity']);
            $line['cart_key'] = $key;
            $items[] = $line;
            $total += $line['subtotal_kopecks'];
            $weight += $line['weight'];
        }
        if ($total > 9999999999) {
            throw ValidationException::withMessages(['cart' => 'Сумма превышает допустимый размер одного заказа. Уменьшите количество.']);
        }

        return ['items' => $items, 'total' => $total / 100, 'weight' => $weight];
    }
}
