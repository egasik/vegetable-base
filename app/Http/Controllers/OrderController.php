<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // Строгая серверная валидация (п. 3.2.2 ТЗ)
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1|max:9999',
        ]);

        $product = Product::findOrFail($request->product_id);
        $quantity = (int) $request->quantity;
        $totalAmount = $product->price * $quantity;

        // Используем транзакцию БД для гарантии целостности данных
        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id' => auth()->id(),
                'total_amount' => $totalAmount,
                'status' => 'pending', // Ожидает обработки
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price_at_moment' => $product->price, // Фиксируем цену на момент покупки (3НФ)
            ]);

            DB::commit();

            return redirect()->route('profile.edit')->with('success', '🎉 Заказ успешно оформлен! Перейдите в личный кабинет для отслеживания.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ошибка при оформлении заказа. Попробуйте позже.');
        }
    }
}