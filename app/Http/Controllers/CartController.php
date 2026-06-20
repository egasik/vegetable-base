<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = session('cart', []);
        $items = [];
        $total = 0;

        foreach ($cart as $cartKey => $item) {
            $product = Product::find($item['product_id']);
            
            if (!$product) {
                $this->removeFromSession($cartKey);
                continue;
            }

            $subtotal = $item['price'] * $item['quantity'];
            $total += $subtotal;
            
            $items[] = [
                'cart_key' => $cartKey,
                'product' => $product,
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'purchase_type' => $item['purchase_type'],
                'subtotal' => $subtotal,
            ];
        }

        return view('cart.index', compact('items', 'total'));
    }

    public function add(Request $request, Product $product)
    {
        $validated = $request->validate([
            'purchase_type' => 'required|in:retail,wholesale',
            'quantity' => 'required|integer|min:1|max:9999',
        ]);

        $cart = session('cart', []);
        $cartKey = $product->id . '_' . $validated['purchase_type'];
        $price = $validated['purchase_type'] === 'retail' 
            ? $product->retail_price 
            : $product->wholesale_price;

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $validated['quantity'];
        } else {
            $cart[$cartKey] = [
                'product_id' => $product->id,
                'purchase_type' => $validated['purchase_type'],
                'quantity' => $validated['quantity'],
                'price' => $price,
            ];
        }

        session(['cart' => $cart]);

        return redirect()->route('cart.index')
            ->with('success', 'Товар добавлен в корзину');
    }

    public function update(Request $request, $cartKey)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:9999',
        ]);

        $cart = session('cart', []);

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] = $validated['quantity'];
            session(['cart' => $cart]);
            return back()->with('success', 'Количество обновлено');
        }

        return back()->with('error', 'Товар не найден в корзине');
    }

    public function remove($cartKey)
    {
        $this->removeFromSession($cartKey);
        return back()->with('success', 'Товар удален из корзины');
    }

    public function clear()
    {
        session()->forget('cart');
        return back()->with('success', 'Корзина очищена');
    }

    private function removeFromSession($cartKey)
    {
        $cart = session('cart', []);
        unset($cart[$cartKey]);
        session(['cart' => $cart]);
    }
    /**
 * Изменить тип покупки товара в корзине
 */
/**
 * Добавить копию товара с другим типом покупки
 * Логика:
 * 1. Создаем новую запись с выбранным типом (старая остается)
 * 2. Если товар с таким типом уже есть — объединяем количество
 * 3. Старую запись НЕ удаляем
 */
public function changeType(Request $request, $cartKey)
{
    $validated = $request->validate([
        'purchase_type' => 'required|in:retail,wholesale',
    ]);

    $cart = session('cart', []);

    if (!isset($cart[$cartKey])) {
        return back()->with('error', 'Товар не найден в корзине');
    }

    $item = $cart[$cartKey];
    $product = Product::find($item['product_id']);

    if (!$product) {
        return back()->with('error', 'Товар больше недоступен');
    }

    // Проверяем доступность выбранного типа
    if ($validated['purchase_type'] === 'retail' && !$product->is_retail) {
        return back()->with('error', 'Розничная продажа недоступна');
    }
    if ($validated['purchase_type'] === 'wholesale' && !$product->is_wholesale) {
        return back()->with('error', 'Оптовая продажа недоступна');
    }

    // Формируем ключ для нового типа
    $newCartKey = $product->id . '_' . $validated['purchase_type'];
    $newPrice = $validated['purchase_type'] === 'retail' 
        ? $product->retail_price 
        : $product->wholesale_price;

    // Если товар с таким типом уже есть — объединяем количество
    if (isset($cart[$newCartKey])) {
        $cart[$newCartKey]['quantity'] += $item['quantity'];
    } else {
        // Создаем новую запись, старая остается
        $cart[$newCartKey] = [
            'product_id' => $product->id,
            'purchase_type' => $validated['purchase_type'],
            'quantity' => $item['quantity'],
            'price' => $newPrice,
        ];
    }

    session(['cart' => $cart]);

    $typeLabel = $validated['purchase_type'] === 'retail' ? 'розницу' : 'опт';
    return back()->with('success', "Добавлен товар в {$typeLabel}");
}
}